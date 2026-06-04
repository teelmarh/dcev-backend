<?php

namespace App\Jobs\Empic;

use App\Exceptions\Empic\EmpicApiException;
use App\Exceptions\Empic\EmpicUnavailableException;
use App\Models\User;
use App\Services\Empic\EmpicCmService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncEmpicAddressJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public int $tries = 3;

    public array $backoff = [60, 120, 300];

    public function __construct(
        public readonly User  $user,
        public readonly array $addressData,
    ) {}

    public function middleware(): array
    {
        return [new WithoutOverlapping('empic-address-' . $this->user->id)];
    }

    public function handle(EmpicCmService $empic): void
    {
        if ($this->user->empic_address_id) {
            // Already added — skip silently
            return;
        }

        if (! $this->user->empic_customer_no) {
            Log::warning('SyncEmpicAddressJob: user has no customerNo — cannot add address', [
                'user_id' => $this->user->id,
            ]);
            // Do not retry — human sync must happen first
            $this->fail(new \RuntimeException('No empic_customer_no on user ' . $this->user->id));
            return;
        }

        try {
            $addressId = $empic->addAddress((int) $this->user->empic_customer_no, $this->addressData);
        } catch (EmpicUnavailableException $e) {
            Log::warning('EMPIC unavailable during addAddress — will retry', [
                'user_id'     => $this->user->id,
                'customer_no' => $this->user->empic_customer_no,
                'error'       => $e->getMessage(),
                'attempt'     => $this->attempts(),
            ]);
            throw $e;
        } catch (EmpicApiException $e) {
            Log::error('EMPIC rejected addAddress payload — no retry', [
                'user_id' => $this->user->id,
                'error'   => $e->getMessage(),
                'context' => $e->context,
            ]);
            $this->fail($e);
            return;
        }

        $this->user->update([
            'empic_address_id' => $addressId,
            'empic_synced'     => true,
        ]);

        Log::info('EMPIC addAddress succeeded', [
            'user_id'    => $this->user->id,
            'address_id' => $addressId,
        ]);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('SyncEmpicAddressJob permanently failed', [
            'user_id' => $this->user->id,
            'error'   => $e->getMessage(),
        ]);
    }
}

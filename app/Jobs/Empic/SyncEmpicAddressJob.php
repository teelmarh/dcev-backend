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
use Illuminate\Support\Facades\Log;

class SyncEmpicAddressJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue;

    public int $tries = 3;

    public array $backoff = [60, 120, 300];

    public function __construct(
        public readonly int   $userId,
        public readonly array $addressData,
    ) {}

    public function middleware(): array
    {
        return [new WithoutOverlapping('empic-address-' . $this->userId)];
    }

    public function handle(EmpicCmService $empic): void
    {
        $user = User::findOrFail($this->userId);

        if ($user->empic_address_id) {
            return;
        }

        if (! $user->empic_customer_no) {
            Log::warning('SyncEmpicAddressJob: user has no customerNo � cannot add address', [
                'user_id' => $this->userId,
            ]);
            $this->fail(new \RuntimeException('No empic_customer_no on user ' . $this->userId));
            return;
        }

        try {
            $addressId = $empic->addAddress((int) $user->empic_customer_no, $this->addressData);
        } catch (EmpicUnavailableException $e) {
            Log::warning('EMPIC unavailable during addAddress � will retry', [
                'user_id' => $this->userId,
                'error'   => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);
            throw $e;
        } catch (EmpicApiException $e) {
            Log::error('EMPIC rejected addAddress payload � no retry', [
                'user_id' => $this->userId,
                'error'   => $e->getMessage(),
                'context' => $e->context,
            ]);
            $this->fail($e);
            return;
        }

        $user->update([
            'empic_address_id' => $addressId,
            'empic_synced'     => true,
            'empic_status'     => 'synced',
        ]);

        Log::info('EMPIC addAddress succeeded', [
            'user_id'    => $this->userId,
            'address_id' => $addressId,
        ]);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('SyncEmpicAddressJob permanently failed', [
            'user_id' => $this->userId,
            'error'   => $e->getMessage(),
        ]);

        User::where('id', $this->userId)->update(['empic_status' => 'failed']);
    }
}

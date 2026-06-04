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

class SyncEmpicHumanJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue;

    public int $tries = 3;

    public array $backoff = [60, 120, 300];

    public function __construct(public readonly int $userId) {}

    public function middleware(): array
    {
        return [new WithoutOverlapping($this->userId)];
    }

    public function handle(EmpicCmService $empic): void
    {
        $user = User::findOrFail($this->userId);

        if ($user->empic_customer_no) {
            return;
        }

        try {
            $customerNo = $empic->createHuman($user);
        } catch (EmpicUnavailableException $e) {
            Log::warning('EMPIC unavailable during createHuman � will retry', [
                'user_id' => $this->userId,
                'error'   => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);
            throw $e;
        } catch (EmpicApiException $e) {
            Log::error('EMPIC rejected createHuman payload � no retry', [
                'user_id' => $this->userId,
                'error'   => $e->getMessage(),
                'context' => $e->context,
            ]);
            $this->fail($e);
            return;
        }

        $user->update(['empic_customer_no' => $customerNo]);

        Log::info('EMPIC createHuman succeeded', [
            'user_id'     => $this->userId,
            'customer_no' => $customerNo,
        ]);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('SyncEmpicHumanJob permanently failed', [
            'user_id' => $this->userId,
            'error'   => $e->getMessage(),
        ]);

        User::where('id', $this->userId)->update(['empic_status' => 'failed']);
    }
}

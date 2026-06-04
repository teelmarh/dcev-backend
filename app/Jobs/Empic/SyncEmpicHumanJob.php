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

class SyncEmpicHumanJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    /**
     * Maximum attempts before the job is marked as failed.
     * 4xx errors short-circuit and do not consume all attempts.
     */
    public int $tries = 3;

    /**
     * Seconds to wait before retrying (exponential-ish).
     */
    public array $backoff = [60, 120, 300];

    public function __construct(public readonly User $user) {}

    /**
     * Prevent overlapping jobs for the same user.
     */
    public function middleware(): array
    {
        return [new WithoutOverlapping($this->user->id)];
    }

    public function handle(EmpicCmService $empic): void
    {
        if ($this->user->empic_customer_no) {
            // Already registered — skip silently
            return;
        }

        try {
            $customerNo = $empic->createHuman($this->user);
        } catch (EmpicUnavailableException $e) {
            Log::warning('EMPIC unavailable during createHuman — will retry', [
                'user_id' => $this->user->id,
                'error'   => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);
            // Re-throw so the queue retries
            throw $e;
        } catch (EmpicApiException $e) {
            Log::error('EMPIC rejected createHuman payload — no retry', [
                'user_id' => $this->user->id,
                'error'   => $e->getMessage(),
                'context' => $e->context,
            ]);
            // Bad input won't fix itself — release without retrying
            $this->fail($e);
            return;
        }

        $this->user->update(['empic_customer_no' => $customerNo]);

        Log::info('EMPIC createHuman succeeded', [
            'user_id'     => $this->user->id,
            'customer_no' => $customerNo,
        ]);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('SyncEmpicHumanJob permanently failed', [
            'user_id' => $this->user->id,
            'error'   => $e->getMessage(),
        ]);
    }
}

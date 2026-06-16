<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditLogger
{
    // Action constants — use these everywhere so action slugs stay consistent
    public const ENROLL_STARTED         = 'enroll_started';
    public const DISCREPANCY_FLAGGED    = 'discrepancy_flagged';
    public const VERIFICATION_COMPLETE  = 'verification_complete';
    public const ESCALATED              = 'escalated';
    public const APPLICATION_PROCESSED  = 'application_processed';
    public const ENROLL_UNCLAIMED       = 'enroll_unclaimed';
    public const APPLICATION_CLAIMED    = 'application_claimed';
    public const APPLICATION_UNCLAIMED  = 'application_unclaimed';
    public const LOGIN                  = 'login';
    public const LOGOUT                 = 'logout';
    public const PASSWORD_RESET         = 'password_reset';
    public const PROFILE_UPDATED        = 'profile_updated';

    /**
     * Write an audit entry. Never throws — failure to log must not block the main operation.
     */
    public static function log(
        User    $officer,
        string  $action,
        Model   $subject,
        array   $payload = [],
        ?Request $request = null
    ): void {
        try {
            AuditLog::create([
                'user_id'      => $officer->id,
                'action'       => $action,
                'subject_type' => get_class($subject),
                'subject_id'   => $subject->getKey(),
                'payload'      => empty($payload) ? null : $payload,
                'ip_address'   => $request?->ip(),
                'user_agent'   => $request?->userAgent(),
            ]);
        } catch (\Throwable) {
            // Log to Laravel's default logger but never surface to the caller
            logger()->error("AuditLogger failed: action={$action} subject=" . get_class($subject) . ":{$subject->getKey()}");
        }
    }
}

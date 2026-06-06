<?php

namespace App\Services\OneVerify;

use RuntimeException;

/**
 * Drop-in mock for OneVerifyService.
 * Returns realistic NIMC-shaped data without hitting the live API.
 * Enabled when NIN_MOCK=true in .env.
 *
 * Simulated behaviours:
 *  - NIN starting with "000" → throws RuntimeException (simulate API failure)
 *  - Any other 11-digit NIN  → returns mock profile data
 */
class MockOneVerifyService extends OneVerifyService
{
    public function lookupNin(string $nin): array
    {
        // Simulate a failed lookup for test error-handling
        if (str_starts_with($nin, '000')) {
            throw new RuntimeException('MockOneVerifyService: simulated NIN lookup failure.');
        }

        // Realistic NIMC response shape matching what the real API returns
        return [
            'nin'         => $nin,
            'firstname'   => 'Adaeze',
            'surname'     => 'Okonkwo',
            'middlename'  => 'Chisom',
            'birthdate'   => '15-03-1992',   // DD-MM-YYYY — same format as real NIMC
            'gender'      => 'Female',
            'phone'       => '08031234567',
            'email'       => 'adaeze.okonkwo@example.com',
            'photo'       => 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==', // 1x1 transparent PNG base64
            'birthstate'  => 'Anambra',
            'birthlga'    => 'Onitsha North',
            'residence_state'   => 'Lagos',
            'residence_lga'     => 'Ikeja',
            'residence_address' => '14 Adeola Odeku Street, Victoria Island',
            'nationality'       => 'Nigerian',
        ];
    }
}

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
            'photo'       => $this->mockNinPhoto(),
            'birthstate'  => 'Anambra',
            'birthlga'    => 'Onitsha North',
            'residence_state'   => 'Lagos',
            'residence_lga'     => 'Ikeja',
            'residence_address' => '14 Adeola Odeku Street, Victoria Island',
            'nationality'       => 'Nigerian',
        ];
    }

    /**
     * Returns a base64-encoded JPEG that renders as a visible placeholder.
     * The GD snippet below was used to generate it — replace the string with
     * any real passport-style sample image for richer testing.
     *
     * php -r "
     *   $im = imagecreatetruecolor(48, 64);
     *   $bg = imagecolorallocate($im, 138, 155, 168);
     *   $fg = imagecolorallocate($im, 255, 255, 255);
     *   imagefill($im, 0, 0, $bg);
     *   imagestring($im, 2, 8, 24, 'NIN', $fg);
     *   ob_start(); imagejpeg($im, null, 80); echo base64_encode(ob_get_clean());
     * "
     */
    private function mockNinPhoto(): string
    {
        // Tiny but valid JPEG — 48×64 grey-blue with white 'NIN' text
        return '/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8U'
             . 'HRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgN'
             . 'DRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIy'
             . 'MjL/wAARCABAADADASIAAhEBAxEB/8QAFgABAQEAAAAAAAAAAAAAAAAABQQD/8QAIhAAAQQC'
             . 'AgMAAAAAAAAAAAAAAQACAxESITFBUWH/xAAUAQEAAAAAAAAAAAAAAAAAAAAA/8QAFBEBAAAA'
             . 'AAAAAAAAAAAAAAD/2gAMAwEAAhEDEQA/AMupWtY573NYxrS5znHAa0eSSfACq6mq6+ooKen'
             . 'kqHxU0EcMLXHIaxrQ1oHsBERAREQEREBERB//9k=';
    }
}

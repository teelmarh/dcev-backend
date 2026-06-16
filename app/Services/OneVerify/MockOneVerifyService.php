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
        // Valid 48×64 JPEG generated with GD — grey-blue background, white 'NIN' text
        return '/9j/4AAQSkZJRgABAQEAYABgAAD//gA7Q1JFQVRPUjogZ2QtanBlZyB2MS4wICh1c2luZyBJSkcgSlBFRyB2ODApLCBxdWFsaXR5ID0gODAK/9sAQwAGBAUGBQQGBgUGBwcGCAoQCgoJCQoUDg8MEBcUGBgXFBYWGh0lHxobIxwWFiAsICMmJykqKRkfLTAtKDAlKCko/9sAQwEHBwcKCAoTCgoTKBoWGigoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgo/8AAEQgAQAAwAwEiAAIRAQMRAf/EAB8AAAEFAQEBAQEBAAAAAAAAAAABAgMEBQYHCAkKC//EALUQAAIBAwMCBAMFBQQEAAABfQECAwAEEQUSITFBBhNRYQcicRQygZGhCCNCscEVUtHwJDNicoIJChYXGBkaJSYnKCkqNDU2Nzg5OkNERUZHSElKU1RVVldYWVpjZGVmZ2hpanN0dXZ3eHl6g4SFhoeIiYqSk5SVlpeYmZqio6Slpqeoqaqys7S1tre4ubrCw8TFxsfIycrS09TV1tfY2drh4uPk5ebn6Onq8fLz9PX29/j5+v/EAB8BAAMBAQEBAQEBAQEAAAAAAAABAgMEBQYHCAkKC//EALURAAIBAgQEAwQHBQQEAAECdwABAgMRBAUhMQYSQVEHYXETIjKBCBRCkaGxwQkjM1LwFWJy0QoWJDThJfEXGBkaJicoKSo1Njc4OTpDREVGR0hJSlNUVVZXWFlaY2RlZmdoaWpzdHV2d3h5eoKDhIWGh4iJipKTlJWWl5iZmqKjpKWmp6ipqrKztLW2t7i5usLDxMXGx8jJytLT1NXW19jZ2uLj5OXm5+jp6vLz9PX29/j5+v/aAAwDAQACEQMRAD8A36KKK6jkCiiigAooooAKKK0V0e6a2tJ/3YiuYppY2LdogxYH3+X6cjnrgCxnUVq2+hz3U1hDazQTTXkTyxopYEBQ3BJAGcqw64yOuMGqFxbPBFbSOVK3EZkXHYB2Xn8VNFwsQ0UUUAFb1lrcMFmltJFI6fYpIOMDbKTNtceo2ykY9yecCsGihq4J2NpdQtI1sfLM7NDZT20gaMAbnEmCDuORmTHbgZ74EGu6kmpvaSrGySpBtmyc75C7szD6ls47Zx0FZlFFh3CiiigQUUUUAFFFFABRRRQB/9k=';
    }
}

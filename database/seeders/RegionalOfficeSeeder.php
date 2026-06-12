<?php

namespace Database\Seeders;

use App\Models\RegionalOffice;
use Illuminate\Database\Seeder;

class RegionalOfficeSeeder extends Seeder
{
    public function run(): void
    {
        $offices = [
            [
                'name'           => 'Lagos Regional Office',
                'slug'           => 'lagos',
                'state'          => 'Lagos',
                'city'           => 'Ikeja',
                'address'        => 'NCAA House, 33 Mobolaji Bank Anthony Way, Ikeja, Lagos',
                'phone'          => '+234-1-497-7670',
                'email'          => 'lagos@ncaa.gov.ng',
                'daily_capacity' => 96,
            ],
            [
                'name'           => 'Abuja Regional Office',
                'slug'           => 'abuja',
                'state'          => 'FCT',
                'city'           => 'Abuja',
                'address'        => 'NCAA Headquarters, Plot 576 Airport Road, Abuja',
                'phone'          => '+234-9-461-7500',
                'email'          => 'abuja@ncaa.gov.ng',
                'daily_capacity' => 96,
            ],
            [
                'name'           => 'Kano Regional Office',
                'slug'           => 'kano',
                'state'          => 'Kano',
                'city'           => 'Kano',
                'address'        => 'Mallam Aminu Kano International Airport, Kano',
                'phone'          => '+234-64-665-017',
                'email'          => 'kano@ncaa.gov.ng',
                'daily_capacity' => 96,
            ],
            [
                'name'           => 'Port Harcourt Regional Office',
                'slug'           => 'port-harcourt',
                'state'          => 'Rivers',
                'city'           => 'Port Harcourt',
                'address'        => 'Port Harcourt International Airport, Omagwa, Rivers State',
                'phone'          => '+234-84-234-870',
                'email'          => 'portharcourt@ncaa.gov.ng',
                'daily_capacity' => 96,
            ],
        ];

        foreach ($offices as $office) {
            RegionalOffice::updateOrCreate(['slug' => $office['slug']], $office);
        }
    }
}

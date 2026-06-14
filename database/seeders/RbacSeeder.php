<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RbacSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            [
                'name'        => 'View Applications',
                'slug'        => 'view_applications',
                'description' => 'Browse and search licence applications',
            ],
            [
                'name'        => 'Process Application',
                'slug'        => 'process_application',
                'description' => 'Update application status (approve / reject / return)',
            ],
            [
                'name'        => 'Manage Appointments',
                'slug'        => 'manage_appointments',
                'description' => 'View, reschedule, or cancel biometric appointments',
            ],
            [
                'name'        => 'View Reports',
                'slug'        => 'view_reports',
                'description' => 'Access statistical and operational reports',
            ],
            [
                'name'        => 'Manage Officers',
                'slug'        => 'manage_officers',
                'description' => 'Create, update, and deactivate officer accounts',
            ],
            [
                'name'        => 'Manage Groups',
                'slug'        => 'manage_groups',
                'description' => 'Create user groups and assign permissions / members',
            ],
            [
                'name'        => 'Oversee Regions',
                'slug'        => 'oversee_regions',
                'description' => 'View appointments and booking metrics across all regional offices',
            ],
            [
                'name'        => 'View Handled Applications',
                'slug'        => 'view_handled_applications',
                'description' => 'View applications that have been processed by officers',
            ],
            [
                'name'        => 'Manage Delivery',
                'slug'        => 'manage_delivery',
                'description' => 'View delivery details and the full dispatch list for pickup and courier items',
            ],
        ];

        foreach ($permissions as $perm) {
            Permission::updateOrCreate(['slug' => $perm['slug']], $perm);
        }

        User::updateOrCreate(
            ['email' => env('SUPERADMIN_EMAIL', 'superadmin@dcev.ncaa.gov.ng')],
            [
                'first_name'        => 'Super',
                'last_name'         => 'Admin',
                'email'             => env('SUPERADMIN_EMAIL', 'superadmin@dcev.ncaa.gov.ng'),
                'password'          => Hash::make(env('SUPERADMIN_PASSWORD', 'Admin@DCEV2026!')),
                'role'              => 'superadmin',
                'email_verified_at' => now(),
            ]
        );
    }
}

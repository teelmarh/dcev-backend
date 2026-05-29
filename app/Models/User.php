<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'nin',
        'first_name',
        'last_name',
        'middle_name',
        'date_of_birth',
        'gender',
        'phone',
        'nationality',
        'birth_state',
        'birth_lga',
        'residence_state',
        'residence_lga',
        'residence_address',
        'nin_photo',
        'nin_verified',
        'nin_verified_at',
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'nin_photo',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'nin_verified_at'   => 'datetime',
            'date_of_birth'     => 'date',
            'nin_verified'      => 'boolean',
            'password'          => 'hashed',
        ];
    }
}

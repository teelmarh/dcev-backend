<?php

namespace App\Models;

use App\Traits\HasPermissions;
use Database\Factories\UserFactory;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmailContract
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasPermissions, MustVerifyEmail, Notifiable;

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
        'nin_photo',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'  => 'datetime',
            'nin_verified_at'    => 'datetime',
            'date_of_birth'      => 'date',
            'nin_verified'       => 'boolean',
            'password'           => 'hashed',
            'regional_office_id' => 'integer',
        ];
    }

    public function licences(): HasMany
    {
        return $this->hasMany(Licence::class);
    }

    public function transactions(): MorphMany
    {
        return $this->morphMany(Transaction::class, 'transactable');
    }

    public function regionalOffice(): BelongsTo
    {
        return $this->belongsTo(RegionalOffice::class);
    }

    public function userGroups(): BelongsToMany
    {
        return $this->belongsToMany(UserGroup::class, 'user_group_user');
    }

    public function directPermissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_permission');
    }
}

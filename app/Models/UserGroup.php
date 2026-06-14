<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class UserGroup extends Model
{
    protected $guarded = [];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_group_permission');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_group_user');
    }
}

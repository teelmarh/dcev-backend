<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $guarded = [];

    public function userGroups(): BelongsToMany
    {
        return $this->belongsToMany(UserGroup::class, 'user_group_permission');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_permission');
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @phan-property User $user
 */
class UserPermission extends Model
{
    protected $hidden = [
        "id",
        "user_id",
        "permission_id",
    ];

    /**
     * Gets the relation to the permission
     *
     * @return HasOne
     */
    public function permission(): HasOne
    {
        return $this->hasOne(
            Permission::class,
            "id",
            "permission_id",
        );
    }

    /**
     * Gets the relation to the user
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

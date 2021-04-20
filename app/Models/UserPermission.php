<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPermission extends Model
{
    /**
     * Gets the relation to the permission
     *
     * @return HasOne
     */
    public function permission(): HasOne
    {
        return $this->hasOne(Permission::class);
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

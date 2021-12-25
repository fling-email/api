<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @phan-property User $user
 */
class LoginToken extends Model
{
    /**
     * List of fields that can be assigned
     *
     * @var string[]
     */
    protected $fillable = [
        "uuid",
        "expires_at",
        "user_id",
        "token",
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var string[]
     */
    protected $hidden = [
        "id",
        "user_id",
    ];

    /**
     * Date attributes
     *
     * @var string[]
     */
    protected $dates = [
        "expires_at",
    ];

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

<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Collection;

/**
 * @property Collection $users
 * @property Collection $domains
 */
class Organisation extends Model
{
    use SoftDeletes;
    use HasFactory;

    /**
     * List of type conversions for attributes
     *
     * @var array
     * @phan-var array<string, string>
     */
    protected $casts = [
        "enabled" => "boolean",
    ];

    /**
     * Gets the relation to the users
     *
     * @return HasMany
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Gets the relation to the domains
     *
     * @return HasMany
     */
    public function domains(): HasMany
    {
        return $this->hasMany(Domain::class);
    }
}

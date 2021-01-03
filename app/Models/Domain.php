<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Domain extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * List of type conversions for attributes
     *
     * @var array
     * @phan-var array<string, string>
     */
    public $casts = [
        "verified" => "boolean",
    ];

    /**
     * List of attributes not included in the json format
     *
     * @var string[]
     */
    public $hidden = [
        "id",
        "organisation_id",
        "dkim_private_key",
    ];

    /**
     * Gets the relation to the organisation
     *
     * @return BelongsTo
     */
    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class EmailRecipient extends Model
{
    /**
     * @var string[]
     */
    protected $fillable = [
        "recipient_id",
        "email_id",
        "type",
    ];

    public $timestamps = false;

    /**
     * Gets the relation to the email
     *
     * @return BelongsTo
     */
    public function email(): BelongsTo
    {
        return $this->belongsTo(Email::class);
    }

    /**
     * Gets the relation to the email recipient
     *
     * @return HasOne
     */
    public function recipient(): HasOne
    {
        return $this->hasOne(
            Recipient::class,
            "id",
            "recipient_id",
        );
    }
}

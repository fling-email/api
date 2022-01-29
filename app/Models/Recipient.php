<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Recipient extends Model
{
    /**
     * Gets the relation to the emails
     *
     * @return HasManyThrough
     */
    public function emails(): HasManyThrough
    {
        return $this->hasManyThrough(Recipient::class, EmailRecipient::class);
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

class Permission extends Model
{
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var string[]
     */
    protected $hidden = [
        "id",
    ];
}

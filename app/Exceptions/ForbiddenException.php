<?php

declare(strict_types=1);

namespace App\Exceptions;

class ForbiddenException extends AppException
{
    /**
     * Gets the HTTP status code for this exception
     *
     * @return integer
     */
    public function status(): int
    {
        return 403;
    }
}

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

    /**
     * Gets the json data for this exception
     *
     * @return array
     * @phan-return array<string, mixed>
     */
    public function json(): array
    {
        return [
            "status" => $this->status(),
            "error" => "Forbidden",
            "message" => $this->getMessage(),
        ];
    }
}

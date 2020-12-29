<?php

declare(strict_types=1);

namespace App\Exceptions;

class UnauthorisedException extends AppException
{
    /**
     * Gets the HTTP status code for this exception
     *
     * @return integer
     */
    public function status(): int
    {
        return 401;
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
            "error" => "Unauthorized",
            "message" => $this->getMessage(),
        ];
    }
}

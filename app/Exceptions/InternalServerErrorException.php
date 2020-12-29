<?php

declare(strict_types=1);

namespace App\Exceptions;

class InternalServerErrorException extends AppException
{
    /**
     * Gets the HTTP status code for this exception
     *
     * @return integer
     */
    public function status(): int
    {
        return 500;
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
            "error" => "Internal Server Error",
            "message" => $this->getMessage(),
        ];
    }
}

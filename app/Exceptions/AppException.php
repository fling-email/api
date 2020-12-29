<?php

declare(strict_types=1);

namespace App\Exceptions;

abstract class AppException extends \Exception
{
    /**
     * @param string $message The error message
     */
    public function __construct(string $message)
    {
        parent::__construct($message, $this->status());
    }

    /**
     * Gets the HTTP status code for this exception
     *
     * @return integer
     */
    abstract public function status(): int;

    /**
     * Renders the json output for exception
     *
     * @return array
     * @phan-return array<string, mixed>
     */
    abstract public function json(): array;
}

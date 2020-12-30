<?php

declare(strict_types=1);

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

abstract class AppException extends \Exception
{
    protected ?string $debug;

    /**
     * @param string $message The error message
     * @param string|null $debug More debug info about the error
     */
    public function __construct(string $message, ?string $debug = null)
    {
        parent::__construct($message, $this->status());

        $this->debug = $debug;
    }

    /**
     * Gets the HTTP status code for this exception
     *
     * @return integer
     */
    abstract public function status(): int;

    /**
     * Gets more debug info about the error. This often contains technical data.
     *
     * @return ?string
     */
    public function getDebug(): ?string
    {
        return $this->debug;
    }

    /**
     * Renders the json output for exception
     *
     * @return array
     * @phan-return array<string, mixed>
     */
    public function json(): array
    {
        $json = [
            "status" => $this->status(),
            "error" => SymfonyResponse::$statusTexts[$this->status()],
            "message" => $this->getMessage(),
        ];

        // Only show useful debug data when running locally as it could reveal
        // informatin useful to attackers.
        if (\app("env") === "local") {
            $json["debug"] = $this->debug;
        }

        return $json;
    }
}

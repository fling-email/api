<?php

declare(strict_types=1);

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

abstract class AppException extends \Exception
{
    protected ?string $debug;
    protected ?array $data;

    /**
     * @param string $message The error message
     * @param string|null $debug More debug info about the error
     * @param array|null $data Data to support the debug information
     */
    public function __construct(string $message, ?string $debug = null, ?array $data = null)
    {
        parent::__construct($message, $this->status());

        $this->debug = $debug;
        $this->data = $data;
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
            $json["data"] = $this->data;
        }

        return $json;
    }
}

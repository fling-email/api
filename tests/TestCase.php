<?php

declare(strict_types=1);

namespace Tests;

use Laravel\Lumen\Testing\TestCase as BaseTestCase;
use Laravel\Lumen\Application;

abstract class TestCase extends BaseTestCase
{
    /**
     * Creates the application.
     *
     * Phan can't handle the dynamic type stuff that goes on here
     * @phan-suppress PhanParamSignatureMismatch
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication(): Application
    {
        require_once __DIR__ . "/../bootstrap/app.php";

        return \createApp();
    }

    /**
     * Performs any setup actions before each test
     *
     * At the moment this recreates the database from scratch, this may have
     * to be changed in the future if performance becomes an issue.
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->artisan("migrate:fresh");
        $this->artisan("db:seed");
    }

    /**
     * Checks the response to see if it looks like a json schema error
     *
     * @return $this
     */
    protected function dontSeeJsonSchemaError(): self
    {
        $data = $this->response->original ?? [];
        $status = $data["status"] ?? 0;
        $error = $data["message"] ?? "";

        if ($status === 500 && $error === "Backend returned an unexpected response") {
            $output_message = <<<MESSAGE
                JSON Schema validation error: {$data["debug"]}
                Response data: {$data["data"]["response"]}
                MESSAGE;

            $this->assertTrue(false, $output_message);
        }

        return $this;
    }
}

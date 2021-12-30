<?php

declare(strict_types=1);

namespace Tests;

use Laravel\Lumen\Testing\TestCase as BaseTestCase;
use App\Application;
use App\Models\User;

abstract class TestCase extends BaseTestCase
{
    private static string $backup_sql_dump_path;

    /**
     * Creates the application.
     *
     * Phan can't handle the dynamic type stuff that goes on here
     * @phan-suppress PhanParamSignatureMismatch
     *
     * @return Application
     */
    public function createApplication(): Application
    {
        return new Application();
    }

    /**
     * Performs any setup actions before each test
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        if (!isset(static::$backup_sql_dump_path)) {
            $this->generateDatabase();
        } else {
            $this->restoreDatabase();
        }
    }

    /**
     * Called after each test is run
     *
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();

        // Make sure we always have a configured $this->app on classes. For some
        // reason generating code coverage tries to access $this->app["config"]
        // which throws an exception if $this->app is null. Ideally we'd only
        // run this after the last test, or even more ideally it would not be
        // needed at all. The performance impact is tiny so just do this for now.
        $this->refreshApplication();
    }

    /**
     * Runs the database migrations and seeds then creates an SQL dump that can
     * be used to quickly restore a known state.
     *
     * @return void
     */
    private function generateDatabase(): void
    {
        $this->artisan("migrate:fresh");
        $this->artisan("db:seed");

        $host = \env("DB_HOST");
        $username = \env("DB_USERNAME");
        $password = \env("DB_PASSWORD");
        $database = \env("DB_DATABASE");

        static::$backup_sql_dump_path = \tempnam(\sys_get_temp_dir(), "phpunit_sql_dump_");

        $sql_dump_data = \shell_exec(
            "mysqldump -h {$host} -u{$username} -p{$password} --skip-comments {$database}"
        );

        \file_put_contents(static::$backup_sql_dump_path, $sql_dump_data);
    }

    /**
     * Restores the database from the previously generated backup
     *
     * @return void
     */
    private function restoreDatabase(): void
    {
        if (!isset(static::$backup_sql_dump_path)) {
            throw new \Exception("Database has not been generated");
        }

        $host = \env("DB_HOST");
        $username = \env("DB_USERNAME");
        $password = \env("DB_PASSWORD");
        $database = \env("DB_DATABASE");
        $file_path = static::$backup_sql_dump_path;

        \exec("mysql -h {$host} -u{$username} -p{$password} {$database} < {$file_path}");
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

    /**
     * Helper method to call $this->actingAs with the test user
     *
     * @return $this
     */
    protected function actingAsTestUser(): self
    {
        return $this->actingAs($this->getTestUser());
    }

    /**
     * Gets the User model for the test user account
     *
     * @return User
     */
    protected function getTestUser(): User
    {
        return User::query()
            ->where("username", "test")
            ->first();
    }

    /**
     * Asserts that some given data is somewhere in the json response
     *
     * @param array $expected The expected data
     * @phan-param array<mixed> $expected
     *
     * @return $this
     */
    protected function seeJsonSubset(array $expected): self
    {
        $this->response->assertJson($expected);

        return $this;
    }

    /**
     * Asserts that the response is a given status
     *
     * @param integer $expected_status The expected HTTP satus code
     * @param ?string $expected_message the expected response message
     *
     * @return $this
     */
    public function seeResponseStatus(int $expected_status, ?string $expected_message = null): self
    {
        $this->assertSame(
            $expected_status,
            $this->response->getStatusCode(),
            \json_encode($this->response->original, \JSON_PRETTY_PRINT),
        );

        if ($expected_message !== null) {
            $this->assertSame(
                $expected_message,
                $this->response->original["message"] ?? "",
            );
        }

        return $this;
    }
}

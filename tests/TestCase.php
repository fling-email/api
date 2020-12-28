<?php

declare(strict_types=1);

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
}

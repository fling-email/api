<?php

declare(strict_types=1);

namespace App\Traits;

use App\Exceptions\MjmlCompilationException;

trait CompilesMjml
{
    /**
     * Compiles a mjml string to HTML
     *
     * @param string $mjml The input mjml
     *
     * @return string
     */
    protected function compileMjml(string $mjml): string
    {
        $proc = \proc_open(
            [$this->getMjmlBinPath(), "-i", "-s"],
            [
                ["pipe", "r"],
                ["pipe", "w"],
                ["pipe", "w"],
            ],
            $pipes,
        );

        \fwrite($pipes[0], $mjml);
        \fclose($pipes[0]);

        $output = \stream_get_contents($pipes[1]);
        $error = \stream_get_contents($pipes[2]);

        \fclose($pipes[1]);
        \fclose($pipes[2]);

        $exit_code = \proc_close($proc);

        if ($exit_code !== 0) {
            throw new MjmlCompilationException("Failed to compile MJML: {$error}");
        }

        return $output;
    }

    /**
     * Gets the full path to the mjml binary
     *
     * @return string
     */
    private function getMjmlBinPath(): string
    {
        return __DIR__ . "/../../node_modules/.bin/mjml";
    }
}

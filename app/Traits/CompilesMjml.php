<?php

declare(strict_types=1);

namespace App\Traits;

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
        // TODO
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

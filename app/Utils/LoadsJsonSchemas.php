<?php

declare(strict_types=1);

namespace App\Utils;

use Swaggest\JsonSchema\Schema;
use Swaggest\JsonSchema\SchemaContract;

trait LoadsJsonSchemas
{
    /**
     * Loads structured data as a json schema
     *
     * @param \stdClass $schema_data The data to load
     *
     * @return SchemaContract
     */
    protected static function loadSchemaData(\stdClass $schema_data): SchemaContract
    {
        $cwd = \getcwd();

        try {
            // cd into the schemas folder while validating so that relative file
            // paths work as expected
            \chdir(static::getSchemaRootPath());

            return Schema::import($schema_data);
        } catch (\Throwable $exception) {
            throw $exception;
        } finally {
            // Make sure the process end up in the same folder after this method
            \chdir($cwd);
        }
    }

    /**
     * Loads a schema json file
     *
     * @param string $file_path The name of the file relative to the schemas folder
     *
     * @return SchemaContract
     */
    protected static function loadSchemaFile(string $file_path): SchemaContract
    {
        // Basic validation on the file path incase we somehow end up with a
        // null byte injection thing going on here.
        if (!(bool) \preg_match("/^[a-z_\/\.]+$/", $file_path)) {
            throw new \InvalidArgumentException("Invalid schema file path");
        }

        $schema_data = \json_decode(\file_get_contents(
            static::getSchemaRootPath() . "/{$file_path}"
        ));

        return static::loadSchemaData($schema_data);
    }

    /**
     * Gets the full path to the schemas folder
     *
     * @return string
     */
    private static function getSchemaRootPath(): string
    {
        return __DIR__ . "/../../schemas";
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit\Utils;

use Tests\TestCase;
use App\Utils\LoadsJsonSchemas;
use betterphp\utils\reflection;
use Swaggest\JsonSchema\Schema;

/**
 * @covers App\Utils\LoadsJsonSchemas
 */
class LoadsJsonSchemasTest extends TestCase
{
    /**
     * Phan wants a return type for the anonymous class here, but that changes
     * every time the function is called so we have to ignore it
     * @suppress PhanPluginUnknownMethodReturnType
     */
    private function getSchemaLoader()
    {
        return new class {
            use LoadsJsonSchemas;
        };
    }

    public function testLoadSchemaData(): void
    {
        $loader = $this->getSchemaLoader();

        $current_cwd = \getcwd();

        $result = reflection::call_method(
            $loader,
            "loadSchemaData",
            [
                (object) [
                    "\$schema" => "https://json-schema.org/draft/2019-09/schema",
                    "const" => "null",
                ],
            ],
        );

        $this->assertInstanceOf(Schema::class, $result);
        $this->assertSame($current_cwd, \getcwd());
    }

    public function testLoadSchemaFile(): void
    {
        $loader = $this->getSchemaLoader();

        $result = reflection::call_method(
            $loader,
            "loadSchemaFile",
            ["error_response.json"],
        );

        $this->assertInstanceOf(Schema::class, $result);
    }

    /**
     * @dataProvider dataLoadSchemaFileWithShadyPath
     */
    public function testLoadSchemaFileWithShadyPath(string|null $char): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid schema file path");

        $loader = $this->getSchemaLoader();

        reflection::call_method(
            $loader,
            "loadSchemaFile",
            ["evil_file.json{$char}trailing data"],
        );
    }

    /**
     * @phan-return list<array{mixed}>
     */
    public function dataLoadSchemaFileWithShadyPath(): array
    {
        return [
            ["\\"],
            [null],
        ];
    }

    public function testGetSchemaRootPath(): void
    {
        $loader = $this->getSchemaLoader();
        $result = reflection::call_method($loader, "getSchemaRootPath");

        $this->assertIsString($result);
        $this->assertStringContainsString("schemas", $result);
        $this->assertDirectoryExists($result);
    }
}

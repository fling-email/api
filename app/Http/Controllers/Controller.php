<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use App\Exceptions\InternalServerErrorException;
use App\Http\Controllers\Controller;
use App\Http\Routes\ControllerRoute;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Swaggest\JsonSchema\Schema;
use Swaggest\JsonSchema\SchemaContract;

abstract class Controller extends BaseController
{
    public static ?string $method = null;
    public static ?string $path = null;
    public static bool $auth = true;

    /**
     * @param Request $request The request being handled
     */
    public function __construct(protected Request $request)
    {
    }

    /**
     * Gets a list of all controllers
     *
     * Note that this uses composers autoloader for the class list. You need to
     * run `composer dump-autoload` when adding or removing classes.
     *
     * @return Collection
     * @phan-return Collection<class-string<Controller>>
     */
    public static function all(): Collection
    {
        $composer_classmap_file = __DIR__ . "/../../../vendor/composer/autoload_classmap.php";

        if (!\file_exists($composer_classmap_file)) {
            throw new InternalServerErrorException("Request handler list not found");
        }

        $composer_classmap = include $composer_classmap_file;

        if (!\is_array($composer_classmap)) {
            throw new InternalServerErrorException("Could not resolve request handler");
        }

        return \collect($composer_classmap)
            ->keys()
            ->filter(fn (string $class_name): bool => (
                // Filter out not-controllers and this class
                Str::startsWith($class_name, __NAMESPACE__) && $class_name !== __CLASS__
            ))
            ->values();
    }

    /**
     * Gets the route for this controller
     *
     * @return ControllerRoute
     */
    public static function getRoute(): ControllerRoute
    {
        return new ControllerRoute(\get_called_class());
    }

    /**
     * Gets the request schema for this controller
     *
     * @return SchemaContract
     */
    public static function getRequestSchema(): SchemaContract
    {
        $schema_file_path = static::getSchemaFilePath("_request");
        $schema_file_data = \json_decode(\file_get_contents($schema_file_path));

        return Schema::import($schema_file_data);
    }

    /**
     * Gets the response schema for this controller
     *
     * @return SchemaContract
     */
    public static function getResponseSchema(): SchemaContract
    {
        $schema_file_path = static::getSchemaFilePath("_response");
        $schema_file_data = \json_decode(\file_get_contents($schema_file_path));

        return Schema::import($schema_file_data);
    }

    /**
     * Gets the path to a schema json file
     *
     * @param string $suffix Suffix to append to the class in the filename
     *
     * @return string
     */
    private static function getSchemaFilePath(string $suffix): string
    {
        $controller_base_name = Str::substr(
            \get_called_class(),
            Str::length(__NAMESPACE__) + 1
        );

        return __DIR__
            . "/../../../schemas/"
            . Str::snake($controller_base_name)
            . "{$suffix}.json";
    }

    /**
     * Gets a list of fields that can be edited via a patch request
     *
     * @return Collection
     * @phan-return Collection<string>
     */
    protected function getEditableProperties(): Collection
    {
        if (static::$method !== "patch") {
            throw new \UnexpectedValueException(
                "Trying to get editable properties for invalid request method " . static::$method
            );
        }

        // Avoid expensive schema parsing when we only want a list of properties
        $file_path = static::getSchemaFilePath("_request");
        $schema_data = \json_decode(\file_get_contents($file_path));

        return \collect($schema_data->properties)
            ->keys();
    }
}

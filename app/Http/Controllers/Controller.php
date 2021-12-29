<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use App\Exceptions\InternalServerErrorException;
use App\Exceptions\BadRequestException;
use App\Http\Controllers\Controller;
use App\Http\Routes\ControllerRoute;
use App\Utils\LoadsJsonSchemas;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Builder as EloquentQueryBuilder;
use Swaggest\JsonSchema\SchemaContract;

abstract class Controller extends BaseController
{
    use LoadsJsonSchemas;

    public static ?string $method = null;
    public static ?string $path = null;
    public static bool $auth = true;
    public static bool $paginated = true;

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

        return static::loadSchemaData($schema_file_data);
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

        // Add the common pagination attributes if this is a paginated response.
        // Means we don't have to maintain the same json in every file :)
        if (static::$paginated) {
            $pagination_properties = static::getPaginationSchemaProperties();

            $schema_file_data->properties ??= new \stdClass();
            $schema_file_data->required ??= [];

            foreach ($pagination_properties as $property => $schema) {
                $schema_file_data->properties->{$property} = $schema;
                $schema_file_data->required[] = $property;
            }
        }

        return static::loadSchemaData($schema_file_data);
    }

    /**
     * Gets the json schema properties for pagination
     *
     * @return array
     * @phan-return array<string, mixed>
     */
    private static function getPaginationSchemaProperties(): array
    {
        return [
            "current_page" => (object) [
                "type" => "integer",
                "min" => 1,
            ],
            "per_page" => (object) [
                "type" => "integer",
                "min" => 1,
                "max" => 100,
            ],
            "from" => (object) [
                "type" => "integer",
                "min" => 1,
            ],
            "to" => (object) [
                "type" => "integer",
                "min" => 1,
            ],
            "total" => (object) [
                "type" => "integer",
                "min" => 1,
            ],
            "last_page" => (object) [
                "type" => "integer",
                "min" => 1,
            ],
            "first_page_url" => (object) [
                "type" => "string",
                "format" => "url",
            ],
            "last_page_url" => (object) [
                "type" => "string",
                "format" => "url",
            ],
            "next_page_url" => (object) [
                "type" => ["string", "null"],
                "format" => "url",
            ],
            "prev_page_url" => (object) [
                "type" => ["string", "null"],
                "format" => "url",
            ],
            "path" => (object) [
                "type" => "string",
                "format" => "url",
            ],
            "links" => (object) [
                "type" => "array",
                "items" => (object) [
                    "type" => "object",
                    "properties" => (object) [
                        "url" => (object) [
                            "type" => ["string", "null"],
                            "format" => "url",
                        ],
                        "label" => (object) [
                            "type" => "string",
                        ],
                        "active" => (object) [
                            "type" => "boolean",
                        ],
                    ],
                    "additionalProperties" => false,
                    "required" => [
                        "url",
                        "label",
                        "active",
                    ],
                ],
            ],
        ];
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

    /**
     * Creates the default response for a model query. This handles pagination
     * if the controller has static::$paginated set to `true`.
     *
     * @param EloquentQueryBuilder $query The models to return
     * @param integer $status_code The HTTP status code to return
     *
     * @return JsonResponse
     */
    protected function jsonResponse(EloquentQueryBuilder $query, int $status_code = 200): JsonResponse
    {
        if (!static::$paginated) {
            return \response()->json(
                $query,
                $status_code,
            );
        } else {
            $per_page = (int) $this->request->get("per_page", 20);
            $page = (int) $this->request->get("page", 1);

            if ($per_page < 1 || $per_page > 100) {
                throw new BadRequestException("Number of items per page must be between 1 and 100");
            }

            return \response()->json(
                $query->paginate(
                    perPage: $per_page,
                    page: $page,
                ),
                $status_code,
            );
        }
    }

    /**
     * Gets the user from the incoming request or throw an exception if there
     * isn't one. This is basically to help Phan understand where users are from
     *
     * @return User
     */
    protected function getRequestUser(): User
    {
        $user = $this->request->user();

        if (!$user instanceof User) {
            throw new \UnexpectedValueException(
                'Controller::getRequestUser() called un an unauthenticated context'
            );
        }

        return $user;
    }
}

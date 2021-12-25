<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

class Model extends EloquentModel
{
    /**
     * Creates a new instance of the model and stores it in the database
     *
     * This is defined so Phan knows the type of variables defined from this
     * function.
     *
     * @param array $attributes Properties for the new model
     * @phan-param array<string, mixed> $attributes
     *
     * @return self
     *
     * phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod.Found
     */
    public static function create(array $attributes): self
    {
        // phpcs:enable
        return parent::create($attributes);
    }

    /**
     * Creates a new instance of this model using it's factory
     *
     * @param array $attributes The attributes to set on the model
     * @phan-param array<string, mixed> $attributes
     *
     * @return static
     */
    public static function factoryCreate(array $attributes): static
    {
        $traits = \class_uses(static::class);

        if ($traits === false || !Arr::has($traits, HasFactory::class)) {
            throw new \BadMethodCallException(
                "Class does not implement the HasFactory methods"
            );
        }

        $factory = static::factory();

        if (!$factory instanceof Factory) {
            throw new \UnexpectedValueException(
                "static::factory() did not return a valid Factory instance"
            );
        }

        $model = $factory->create($attributes);

        if (!$model instanceof static) {
            throw new \UnexpectedValueException(
                "Factory did not create an instance of " . static::class
            );
        }

        return $model;
    }
}

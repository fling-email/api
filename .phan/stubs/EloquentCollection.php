<?php

namespace Illuminate\Database\Eloquent;

use Illuminate\Contracts\Queue\QueueableCollection;
use Illuminate\Support\Collection as BaseCollection;

/**
 * Minimal version of an Eloquent Collection defined as a generic so that Phan
 * knows more return types. This is not intended to be a complete stub for the
 * collection class, only the most commonly used methods are defined here.
 *
 * @template T
 */
abstract class Collection extends BaseCollection implements QueueableCollection
{
    /** @phan-var T[] */
    protected $items = [];

    /**
     * @phan-param T[] $items
     */
    public function __construct($items)
    {
    }

    /**
     * @phan-return T[]
     */
    public function all()
    {
    }

    /**
     * @phan-return T
     */
    public function first()
    {
    }

    /**
     * @phan-return static<T>
     */
    public function filter()
    {
    }

    /**
     * @phan-param callable $callback
     * @phan-return static
     */
    public function map(callable $callback)
    {
    }

    /**
     * @phan-param mixed $key
     * @phan-param mixed $default
     * @phan-return T|static|null
     */
    public function find($key, $default = null)
    {
    }
}

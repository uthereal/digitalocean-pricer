<?php

namespace App\Concerns;

trait Makeable
{
    /**
     * Create a new item.
     *
     * @param  mixed  ...$arguments
     * @return static
     */
    public static function make(...$arguments): static
    {
        return new static(...$arguments);
    }
}

<?php

namespace App\Concerns;

trait Memoize
{
    /** @var array */
    protected array $cache = [];

    /**
     * Remember a callback for a calling function.
     *
     * @param  string  $key
     * @param  callable  $callback
     * @return mixed
     */
    public function remember(string $key, callable $callback): mixed
    {
        if (!array_key_exists($key, $this->cache)) {
            $this->cache[$key] = $callback();
        }

        return $this->cache[$key];
    }
}
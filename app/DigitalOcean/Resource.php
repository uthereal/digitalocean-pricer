<?php

namespace App\DigitalOcean;

use App\Concerns\Makeable;
use App\Services\DigitalOcean;

abstract class Resource
{
    use Makeable;

    /**
     * @param  array  $data
     * @param  string  $token
     */
    public function __construct(
        protected readonly array $data,
        protected readonly string $token,
    ) {
        //...
    }

    /**
     * @param  string  $url
     * @param  string  $token
     * @return \App\DigitalOcean\Resource
     */
    public static function FromUrl(string $url, string $token): Resource
    {
        return static::make(app(DigitalOcean::class)->url($token, $url), $token);
    }

    /**
     * Compute the monthly cost for this resource.
     *
     * @return float
     */
    abstract public function getMonthlyCost(): float;
}

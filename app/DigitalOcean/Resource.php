<?php

namespace App\DigitalOcean;

use App\Concerns\Makeable;
use App\Concerns\Memoize;
use App\Services\DigitalOcean;

abstract class Resource
{
    use Makeable;
    use Memoize;

    /**
     * @param  \App\Services\DigitalOcean  $digitalOceanApi
     * @param  array  $data
     * @param  string  $token
     */
    public function __construct(
        protected readonly DigitalOcean $digitalOceanApi,
        protected readonly array $data,
        protected readonly string $token,
    ) {
        //...
    }

    /**
     * @param  string  $url
     * @param  string  $token
     * @return \App\DigitalOcean\Resource
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public static function FromUrl(string $url, string $token): Resource
    {
        return static::make(app(DigitalOcean::class), app(DigitalOcean::class)->url($token, $url), $token);
    }

    /**
     * Get the name of this resource
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->remember(__FUNCTION__, fn() => $this->name());
    }


    /**
     * Compute the monthly cost for this resource
     *
     * @return float
     */
    public function getMonthlyCost(): float
    {
        return $this->remember(__FUNCTION__, fn() => $this->monthlyCost());
    }

    /**
     * Get the name of this resource
     *
     * @return string
     */
    abstract protected function name(): string;

    /**
     * Compute the monthly cost for this resource
     *
     * @return float
     */
    abstract protected function monthlyCost(): float;
}

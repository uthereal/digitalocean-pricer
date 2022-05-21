<?php

namespace App\DigitalOcean;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class AppPlatform extends Resource
{
    /**
     * Return a list of prices
     *
     * @return \Illuminate\Support\Collection
     */
    protected static function Prices(): Collection
    {
        return once(
            fn() => collect(json_decode(File::get(base_path('content/prices/app-platform.json')), true))
        );
    }

    /**
     * @inheritDoc
     */
    protected function name(): string
    {
       return $this->data['app']['spec']['name'];
    }

    /**
     * @link https://docs.digitalocean.com/products/app-platform/#container-pricing
     * @return float
     */
    public function monthlyCost(): float
    {
        $sum = 0;

        foreach ($this->data['app']['spec']['services'] as $service) {
            $price = self::Prices()[$service['instance_size_slug']];
            $count = $service['instance_count'];

            $sum += $price['monthly_price'] * $count;
        }

        return $sum;
    }
}
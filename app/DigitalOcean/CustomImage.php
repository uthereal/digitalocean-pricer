<?php

namespace App\DigitalOcean;

class CustomImage extends Resource
{
    /** @var float */
    protected static float $CostPerGb = 0.05;

    /**
     * @inheritDoc
     */
    protected function name(): string
    {
        return $this->data['name'];
    }

    /**
     * @link https://docs.digitalocean.com/products/images/custom-images/details/pricing
     * @return float
     */
    public function monthlyCost(): float
    {
        return $this->data['size_gigabytes'] * self::$CostPerGb;
    }
}
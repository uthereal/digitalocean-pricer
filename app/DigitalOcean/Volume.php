<?php

namespace App\DigitalOcean;

class Volume extends Resource
{
    /** @var float */
    protected static float $PricePerGb = 0.10;

    /**
     * @inheritDoc
     */
    protected function name(): string
    {
        return $this->data['volume']['id'];
    }

    /**
     * @link https://docs.digitalocean.com/products/volumes/details/pricing
     * @return float
     */
    public function monthlyCost(): float
    {
        return $this->data['volume']['size_gigabytes'] * self::$PricePerGb;
    }
}

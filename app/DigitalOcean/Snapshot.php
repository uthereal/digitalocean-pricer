<?php

namespace App\DigitalOcean;

class Snapshot extends Resource
{
    /**
     * @var float[]
     */
    protected static array $PricePerGB = [
        'droplet' => 0.05,
        'volume' => 0.05,
    ];

    /**
     * @inheritDoc
     */
    protected function name(): string
    {
        return $this->data['snapshot']['name'];
    }

    /**
     * @link https://docs.digitalocean.com/products/images/snapshots/details/pricing
     * @return float
     */
    public function monthlyCost(): float
    {
        $type = $this->data['snapshot']['resource_type'];

        return $this->data['snapshot']['size_gigabytes'] * self::$PricePerGB[$type];
    }
}

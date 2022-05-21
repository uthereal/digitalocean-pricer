<?php

namespace App\DigitalOcean;

class ContainerRegistry extends Resource
{
    /**
     * @inheritDoc
     */
    protected function name(): string
    {
        return $this->data['registry']['name'];
    }

    /**
     * @link https://docs.digitalocean.com/products/container-registry/#plans-and-pricing
     * @return float
     */
    public function monthlyCost(): float
    {
        return $this->data['subscription']['tier']['monthly_price_in_cents'] / 100;
    }
}
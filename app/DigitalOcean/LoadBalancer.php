<?php

namespace App\DigitalOcean;

class LoadBalancer extends Resource
{
    /** @var float */
    protected static float $PricePerNode = 10.00;

    /**
     * @inheritDoc
     */
    protected function name(): string
    {
        return $this->data['load_balancer']['name'];
    }

    /**
     * @link https://docs.digitalocean.com/products/networking/load-balancers/#plans-and-pricing
     * @return float
     */
    public function monthlyCost(): float
    {
        return self::$PricePerNode * $this->data['load_balancer']['size_unit'];
    }
}
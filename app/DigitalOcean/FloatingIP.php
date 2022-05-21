<?php

namespace App\DigitalOcean;

class FloatingIP extends Resource
{
    /** @var float */
    protected static float $NotAssignedPrice = 4.00;

    /**
     * @inheritDoc
     */
    protected function name(): string
    {
        return $this->data['floating_ip']['ip'];
    }

    /**
     * @return float
     * @link https://docs.digitalocean.com/products/networking/floating-ips/details/pricing
     */
    public function monthlyCost(): float
    {
        $droplet = $this->data['floating_ip']['droplet'];

        return $droplet ? 0.00 : self::$NotAssignedPrice;
    }
}

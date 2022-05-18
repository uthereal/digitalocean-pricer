<?php

namespace App\DigitalOcean;

use Illuminate\Support\Arr;

class FloatingIP extends Resource
{
    /**
     * @link https://docs.digitalocean.com/products/networking/floating-ips
     * @var float
     */
    protected static float $NotAssignedPrice = 4.00;

    /**
     * @return float
     */
    public function getMonthlyCost(): float
    {
        $droplet = Arr::get($this->data, 'floating_ip.droplet');

        return $droplet ? 0.00 : $this::$NotAssignedPrice;
    }
}

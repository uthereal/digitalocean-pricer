<?php

namespace App\DigitalOcean;

class Backup extends Resource
{
    /** @var float */
    protected static float $AddedPercentCost = 0.20;

    /**
     * @inheritDoc
     */
    protected function name(): string
    {
        return "Backup for {$this->data['droplet']['name']}";
    }

    /**
     * @link https://docs.digitalocean.com/products/images/backups/details/pricing/
     * @return float
     */
    public function monthlyCost(): float
    {
        return $this->data['droplet']['size']['price_monthly'] * self::$AddedPercentCost;
    }
}
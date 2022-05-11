<?php

namespace App\DigitalOcean;

use Illuminate\Support\Arr;

class Database extends Resource
{
    /** @var float[] */
    protected static array $Prices = [
        // Databases
        'db-s-1vcpu-1gb' => 15.00,
    ];

    /**
     * @return float
     */
    public function getMonthlyCost(): float
    {
        $size = Arr::get($this->data, 'database.size');

        return Arr::get($this::$Prices, $size, 0.00);
    }
}

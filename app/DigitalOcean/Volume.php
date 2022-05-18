<?php

namespace App\DigitalOcean;

use Illuminate\Support\Arr;

class Volume extends Resource
{
    /**
     * @link
     * @var float
     */
    protected static float $PricePerGb = 0.10;

    /**
     * @return float
     */
    public function getMonthlyCost(): float
    {
        return $this::$PricePerGb * Arr::get($this->data, 'volume.size_gigabytes');
    }
}

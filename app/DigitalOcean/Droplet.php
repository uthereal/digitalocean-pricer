<?php

namespace App\DigitalOcean;

use App\Services\DigitalOcean;
use Illuminate\Support\Arr;

class Droplet extends Resource
{
    /**
     * @return float
     */
    public function getMonthlyCost(): float
    {
        return Arr::get($this->data, 'droplet.size.price_monthly', 0.00);
    }
}

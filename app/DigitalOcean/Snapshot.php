<?php

namespace App\DigitalOcean;

use App\Services\DigitalOcean;
use Illuminate\Support\Arr;

class Snapshot extends Resource
{
    /**
     * @link https://docs.digitalocean.com/products/images/snapshots/
     * @var float[]
     */
    protected static array $PricePerGB =[
        'droplet' => 0.05,
        'volume' => 0.05,
    ];

    /**
     * @return float
     */
    public function getMonthlyCost(): float
    {
        $type=Arr::get($this->data, 'snapshot.resource_type');
        $pricePerGb = Arr::get($this::$PricePerGB, $type);

        abort_if(is_null($pricePerGb), 500, "Unknown price per gb for {$type}");

        return $pricePerGb*  Arr::get($this->data, 'snapshot.size_gigabytes');
    }
}

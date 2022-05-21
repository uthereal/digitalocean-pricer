<?php

namespace App\DigitalOcean;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class Database extends Resource
{
    /**
     * Return a list of prices
     *
     * @return \Illuminate\Support\Collection
     */
    protected static function Prices(): Collection
    {
        return once(
            fn() => collect(json_decode(File::get(base_path('content/prices/databases.json')), true))
        );
    }

    /**
     * @inheritDoc
     */
    protected function name(): string
    {
        return $this->data['database']['name'];
    }

    /**
     * @link https://docs.digitalocean.com/products/databases
     * @return float
     */
    public function monthlyCost(): float
    {
        $size = $this->data['database']['size'];
        $count = $this->data['database']['num_nodes'];
        $engine =  $this->data['database']['engine'];
        $standByNodes = $count - 1;

        $price = $this::Prices()[$engine][$size];

        return $price['monthly_price'] + ($standByNodes * $price['standby_monthly_price']);
    }
}

<?php

namespace App\DigitalOcean;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class Database extends Resource
{
    /**
     * Return a list of prices for
     *
     * @return \Illuminate\Support\Collection
     */
    protected static function Prices(): Collection
    {
        return collect(json_decode(File::get(base_path('content/prices/databases.json')), true));
    }

    /**
     * @return float
     */
    public function getMonthlyCost(): float
    {
        $size = Arr::get($this->data, 'database.size');
        $count = Arr::get($this->data, 'database.num_nodes');
        $engine = Arr::get($this->data, 'database.engine');
        $standByNotes = $count - 1;

        $enginePrices = $this::Prices()->get($engine, []);
        $price = Arr::get($enginePrices, $size);

        abort_if(is_null($price), 500, "Invalid DigitalOcean price for database {$engine} with size {$size}");

        return $price['monthly_price'] + ($standByNotes * $price['standby_monthly_price']);
    }
}

<?php

namespace App\DigitalOcean;

use Illuminate\Support\Arr;

class Droplet extends Resource
{
    /**
     * @return float
     * @throws \Exception
     */
    public function getMonthlyCost(): float
    {
        $price = Arr::get($this->data, 'droplet.size.price_monthly');

        foreach (Arr::get($this->data, 'droplet.snapshot_ids', []) as $snapshot) {
            $price += Snapshot::make(
                $this->digitalOceanApi,
                $this->digitalOceanApi->snapshot($this->token, $snapshot),
                $this->token
            )->getMonthlyCost();
        }

        abort_if(is_null($price), 500, "Price for droplet not found");

        return $price;
    }
}

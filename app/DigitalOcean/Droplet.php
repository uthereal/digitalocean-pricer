<?php

namespace App\DigitalOcean;

class Droplet extends Resource
{
    /**
     * @inheritDoc
     */
    protected function name(): string
    {
        return $this->data['droplet']['name'];
    }

    /**
     * @link https://docs.digitalocean.com/products/droplets/#plans-and-pricing
     * @return float
     * @throws \Exception
     */
    public function monthlyCost(): float
    {
        $price = $this->data['droplet']['size']['price_monthly'];

        // Compute backup costs
        if (in_array('backups', $this->data['droplet']['features'])) {
            $price += Backup::make($this->digitalOceanApi, $this->data, $this->token)->getMonthlyCost();
        }

        // Compute snapshot costs
        foreach ($this->data['droplet']['snapshot_ids'] as $snapshot) {
            $price += Snapshot::make(
                $this->digitalOceanApi,
                $this->digitalOceanApi->snapshot($this->token, $snapshot),
                $this->token
            )->getMonthlyCost();
        }

        return $price;
    }
}

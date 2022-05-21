<?php

namespace App\DigitalOcean;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class Kubernetes extends Resource
{
    /**
     * Return a list of prices
     *
     * @return \Illuminate\Support\Collection
     */
    protected static function Prices(): Collection
    {
        return once(
            fn() => collect(json_decode(File::get(base_path('content/prices/kubernetes.json')), true))
        );
    }

    /**
     * @inheritDoc
     */
    protected function name(): string
    {
        return $this->data['kubernetes_cluster']['name'];
    }

    /**
     * @return float
     * @link https://docs.digitalocean.com/products/kubernetes/#plans-and-pricing
     */
    public function monthlyCost(): float
    {
        $price = 0;
        $highAvailabilityIsFree = false;
        $nodePrices = self::Prices()['nodes'];
        $haPrice = self::Prices()['ha'];

        foreach ($this->data['kubernetes_cluster']['node_pools'] as $pool) {
            $node = $nodePrices[$pool['size']];
            $nodeCount = count($pool['nodes']);

            $highAvailabilityIsFree |= ($node['professional'] && $nodeCount >= 3);
            $price += $node['monthly_price'] * $nodeCount;
        }

        if (!$highAvailabilityIsFree) {
            $price += $haPrice;
        }

        return $price;
    }
}
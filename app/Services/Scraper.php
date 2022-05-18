<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Scraper
{

    /**
     * Return a collection of database prices
     *
     * @param  string  $cookie
     * @return \Illuminate\Support\Collection
     * @throws \Exception
     */
    public function digitalOceanDatabasePrices(string $cookie): Collection
    {
        return Http::withCookies([
            '_digitalocean2_session_v4' => $cookie,
        ], 'cloud.digitalocean.com')
            ->get('https://cloud.digitalocean.com/graphql', [
                'operationName' => 'DBaaSGetPlans',
                'query' => "query DBaaSGetPlans {DBaaSGetPlans {\n    redis {\n      plans {\n        name\n        monthly_price\n        v_cpu\n        ram_total\n        usable_memory\n        disk_size\n        excluded_layouts\n        size_category\n        droplet_slug_name\n        enabled_regions\n        standby_monthly_price\n        replica_monthly_price\n        __typename\n      }\n      __typename\n    }\n    mysql {\n      plans {\n        name\n        monthly_price\n        v_cpu\n        ram_total\n        usable_memory\n        disk_size\n        excluded_layouts\n        size_category\n        droplet_slug_name\n        enabled_regions\n        standby_monthly_price\n        replica_monthly_price\n        __typename\n      }\n      __typename\n    }\n    postgres {\n      plans {\n        name\n        monthly_price\n        v_cpu\n        ram_total\n        usable_memory\n        disk_size\n        excluded_layouts\n        size_category\n        droplet_slug_name\n        enabled_regions\n        standby_monthly_price\n        replica_monthly_price\n        __typename\n      }\n      __typename\n    }\n    mongodb {\n      plans {\n        name\n        monthly_price\n        v_cpu\n        ram_total\n        usable_memory\n        disk_size\n        excluded_layouts\n        size_category\n        droplet_slug_name\n        enabled_regions\n        standby_monthly_price\n        replica_monthly_price\n        __typename\n      }\n      __typename\n    }\n    __typename\n  }\n}\n"
            ])->collect('data.DBaaSGetPlans')
            ->filter(fn($engine) => is_array($engine))
            ->map(function (array $engine) {
                $plans = new Collection();
                foreach ($engine['plans'] as $plan) {
                    $planSlug = $plan['droplet_slug_name'];

                    // Prefix basic nodes with "db-"
                    if(Str::startsWith($planSlug, ['s-', 'm-'])) {
                        $planSlug = "db-{$planSlug}";
                    }

                    $plans->put($planSlug, [
                        'monthly_price' => floatval($plan['monthly_price']),
                        'standby_monthly_price' => floatval($plan['standby_monthly_price']),
                        'replica_monthly_price' => floatval($plan['replica_monthly_price']),
                    ]);
                }

                return $plans;
            });
    }
}
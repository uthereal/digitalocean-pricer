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
    public function digitalOceanAppPlatformPrices(string $cookie): Collection
    {
        return Http::withCookies([
            '_digitalocean2_session_v4' => $cookie,
        ], 'cloud.digitalocean.com')
            ->get('https://cloud.digitalocean.com/graphql', [
                'operationName' => 'ListInstanceSizes',
                'query' => "query ListInstanceSizes {\n  ListInstanceSizes {\n    instance_sizes {\n      name\n      slug\n      cpu_type\n      cpus\n      memory_bytes\n      usd_per_month\n      usd_per_second\n      tier_slug\n      tier_upgrade_to\n      tier_downgrade_to\n      __typename\n    }\n    __typename\n  }\n}\n"
            ])->collect('data.ListInstanceSizes.instance_sizes')
            ->filter(fn($engine) => is_array($engine))
            ->mapWithKeys(function (array $instance) {
                return [
                    $instance['slug'] => [
                        'monthly_price' => floatval($instance['usd_per_month']),
                    ]
                ];
            });
    }

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
                return collect($engine['plans'])->mapWithKeys(function($plan) {
                    $planSlug = $plan['droplet_slug_name'];

                    // Prefix basic nodes with "db-"
                    if (Str::startsWith($planSlug, ['s-', 'm-'])) {
                        $planSlug = "db-{$planSlug}";
                    }

                    return [
                        $planSlug => [
                            'monthly_price' => floatval($plan['monthly_price']),
                            'standby_monthly_price' => floatval($plan['standby_monthly_price']),
                            'replica_monthly_price' => floatval($plan['replica_monthly_price']),
                        ]
                    ];
                })->toArray();
            });
    }

    /**
     * Return a collection of kubernetes prices
     *
     * @param  string  $cookie
     * @return \Illuminate\Support\Collection
     * @throws \Exception
     */
    public function digitalOceanKubernetesPrices(string $cookie): Collection
    {
        return Http::withCookies([
            '_digitalocean2_session_v4' => $cookie,
        ], 'cloud.digitalocean.com')
            ->get('https://cloud.digitalocean.com/graphql', [
                'operationName' => 'k8sGetOptionsForCreate',
                'query' => "fragment NodeSizeTierFragment on K8sNodeSize {\n  tier {\n    id\n    __typename\n  }\n  __typename\n}\n\nquery k8sGetOptionsForCreate(\$payload: K8sGetOptionsReq = {include_disallowed_sizes: false}) {\n  K8sGetOptions(K8sGetOptionsReq: \$payload) {\n    options {\n      defaults {\n        region_slug\n        size_slug\n        node_count\n        version_slug\n        __typename\n      }\n      regions {\n        id\n        slug\n        name\n        geography\n        restriction\n        features_enabled\n        features_disabled\n        __typename\n      }\n      node_sizes {\n        id\n        slug\n        name\n        available_region_slugs\n        available_region_ids\n        cpu_count\n        memory_bytes\n        disk_bytes\n        bandwidth_bytes\n        usable_memory_bytes\n        price_per_hour\n        price_per_month\n        allowed_for_create\n        hide_in_ui\n        category {\n          id\n          name\n          __typename\n        }\n        ...NodeSizeTierFragment\n        __typename\n      }\n      versions {\n        slug\n        supported_features\n        __typename\n      }\n      ha_options {\n        PricePerHour\n        PricePerMonth\n        __typename\n      }\n      __typename\n    }\n    __typename\n  }\n}\n"
            ])->collect('data.K8sGetOptions.options')
            ->filter(fn($option, $key) => in_array($key, ['ha_options', 'node_sizes']))
            ->mapWithKeys(function (array $option, $key) {
                return match ($key) {
                    'ha_options' => [
                        'ha' => $option['PricePerMonth']
                    ],
                    'node_sizes' => [
                        'nodes' => collect($option)->mapWithKeys(function ($item) {
                            return [
                                $item['slug'] => [
                                    'monthly_price' => $item['price_per_month'],
                                    'professional' => $item['tier']['id'] == 'Professional',
                                ]
                            ];
                        })->toArray()
                    ],
                    default => null,
                };
            })->filter();
    }
}
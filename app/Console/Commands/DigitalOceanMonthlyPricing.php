<?php

namespace App\Console\Commands;

use App\DigitalOcean\Database;
use App\DigitalOcean\Droplet;
use App\DigitalOcean\Space;
use App\Services\DigitalOcean;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class DigitalOceanMonthlyPricing extends Command
{
    /** @var string */
    protected $signature = 'do:monthly-cost
                            {token : DigitalOcean Api Token}';

    /** @var string */
    protected $description = 'Get a monthly breakdown of pricing for DigitalOcean servers';

    /**
     * @param  \App\Services\DigitalOcean  $digitalOceanApi
     */
    public function __construct(
        protected DigitalOcean $digitalOceanApi,
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $token = $this->argument('token');
        $totals = new Collection();

        // Alerts
        $this->alert('ALERT');
        $this->warn('DigitalOcean Spaces API does not allow for the full computation of pricing. Actual pricing may vary');
        $this->line('');

        foreach ($this->digitalOceanApi->projects($token) as $project) {
            $this->line("Project <options=bold>{$project['name']}</> with UUID <options=bold>{$project['id']}</>", verbosity: 'v');

            $sum = 0;
            $resourceCount = 0;
            foreach ($this->digitalOceanApi->projectResources($token, $project['id']) as $resource) {
                $this->line("\tResource: {$resource['links']['self']}", verbosity: 'vv');

                $price = 0;
                if (Str::contains($resource['links']['self'], 'droplets')) {
                    $price = Droplet::FromUrl($resource['links']['self'], $token)->getMonthlyCost();
                } else if (Str::contains($resource['links']['self'], 'databases')) {
                    $price = Database::FromUrl($resource['links']['self'], $token)->getMonthlyCost();
                } else if (Str::contains($resource['links']['self'], 'digitaloceanspaces')) {
                    $price = Space::FromUrl($resource['links']['self'], $token)->getMonthlyCost();
                } else if (Str::contains($resource['links']['self'], 'domains')) {
                    // Can ignore
                } else {
                    $this->error("\t\t...Unknown resource type");
                }

                $sum += $price;
                $resourceCount += 1;
            }

            $totals->push([
                'client' => $project['name'],
                'cost' => $sum,
                'cost_formatted' => '$ '.number_format($sum, 2).' / month',
                'cost_year_formatted' => '$ '.number_format($sum * 12, 2).' / year',
                'resource_count' => $resourceCount,
            ]);
        }

        $this->table(
            ['Client', 'Cost per Month (USD)', 'Cost per Year (USD)', 'Resource Count'],
            $totals->map(fn(array $item) => Arr::only($item, ['client', 'cost_formatted', 'cost_year_formatted', 'resource_count'])),
        );
        $total = '$ '.number_format($totals->sum('cost'), 2);
        $this->line("Total: <fg=green;options=bold>{$total}</>");
        $this->line('');

        return static::SUCCESS;
    }
}

<?php

namespace App\Commands;

use App\DigitalOcean\AppPlatform;
use App\DigitalOcean\ContainerRegistry;
use App\DigitalOcean\CustomImage;
use App\DigitalOcean\Database;
use App\DigitalOcean\Droplet;
use App\DigitalOcean\FloatingIP;
use App\DigitalOcean\Kubernetes;
use App\DigitalOcean\LoadBalancer;
use App\DigitalOcean\Resource;
use App\DigitalOcean\Space;
use App\DigitalOcean\Volume;
use App\Services\DigitalOcean;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;

class DigitalOceanMonthlyPricing extends Command
{
    /** @var string */
    protected $signature = 'monthly-cost
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
        $this->title("DigitalOcean Monthly Project Pricing");

        $token = $this->argument('token');
        // Alerts
        $this->alert('Some usage based pricing may vary.');
        $this->newLine();

        $projects = $this->getProjectResources($token);

        $this->printTable($projects);

        return static::SUCCESS;
    }

    /**
     * Function for project resource retrieval
     *
     * @param  string  $token
     * @return \Illuminate\Support\Collection
     */
    protected function getProjectResources(string $token): Collection
    {
        $projects = new Collection();

        // Price projects and their associated costs
        $this->line('Pricing out projects');
        foreach ($this->digitalOceanApi->projects($token) as $project) {
            $this->line("Project <options=bold>{$project['name']}</> with UUID <options=bold>{$project['id']}</>", verbosity: 'normal');

            $projects->push(
                Collection::make([
                    'name' => $project['name'],
                    'resources' => $this->digitalOceanApi->projectResources($token, $project['id'])->map(function ($resource) use ($token) {
                        $this->line("\tResource: {$resource['links']['self']}", verbosity: 'v');

                        $doResource = null;
                        if (Str::contains($resource['links']['self'], 'apps')) { // App Platform
                            $doResource = AppPlatform::FromUrl($resource['links']['self'], $token);
                        } else if (Str::contains($resource['links']['self'], 'apps')) { // Container Registry
                            $doResource = ContainerRegistry::FromUrl($resource['links']['self'], $token);
                        } else if (Str::contains($resource['links']['self'], 'databases')) { // Databases
                            $doResource = Database::FromUrl($resource['links']['self'], $token);
                        } else if (Str::contains($resource['links']['self'], 'droplets')) { // Droplet
                            $doResource = Droplet::FromUrl($resource['links']['self'], $token);
                        } else if (Str::contains($resource['links']['self'], 'floating_ips')) { // Floating Ip
                            $doResource = FloatingIP::FromUrl($resource['links']['self'], $token);
                        } else if (Str::contains($resource['links']['self'], 'kubernetes')) { // Kubernetes
                            $doResource = Kubernetes::FromUrl($resource['links']['self'], $token);
                        } else if (Str::contains($resource['links']['self'], 'load_balancers')) { // Load Balancer
                            $doResource = LoadBalancer::FromUrl($resource['links']['self'], $token);
                        } else if (Str::contains($resource['links']['self'], 'digitaloceanspaces')) { // Spaces
                            $doResource = Space::FromUrl($resource['links']['self'], $token);
                        } else if (Str::contains($resource['links']['self'], 'volumes')) { // Volumes
                            $doResource = Volume::FromUrl($resource['links']['self'], $token);
                        } else if (Str::contains($resource['links']['self'], 'domains')) { // Domains
                            // Can ignore
                        } else {
                            $this->error("\t\t...Unknown resource type{$resource['links']['self']}");
                        }

                        return $doResource;
                    })->collect(),
                ])
            );
        }

        // Price container registry if exists
        $this->line('Pricing out container registry');
        if ($data = $this->digitalOceanApi->containerRegistry($token)) {
            $resource = ContainerRegistry::make($this->digitalOceanApi, $data, $token);
            $projects->push(
                Collection::make([
                    'name' => 'Container Registry',
                    'resources' => Collection::make([$resource]),
                ])
            );
        }

        // Price custom images
        $this->line('Pricing out custom images');
        $images = $this->digitalOceanApi->customImages($token);
        if ($images->isNotEmpty()) {
            $projects->push(
                Collection::make([
                    'name' => 'Custom Images',
                    'resources' => $images->map(function ($image) use ($token) {
                        return CustomImage::make($this->digitalOceanApi, $image, $token);
                    })->collect()
                ])
            );
        }

        return $projects;
    }

    /**
     * Print out the calculation information
     *
     * @param  \Illuminate\Support\Collection  $projects
     * @return void
     */
    protected function printTable(Collection $projects): void
    {
        $total = $projects->sum(
            fn(Collection $project) => $project->get('resources')->sum(fn(Resource $resource) => $resource->getMonthlyCost())
        );

        foreach ($projects as $project) {
            $this->newLine();
            $this->line("Project <fg=green;options=underscore>{$project['name']}</>");
            $this->table(
                ['Resource Name', 'Cost per Month (USD)'],
                $project['resources']->map(function (Resource $resource) {
                    return [
                        $resource->getName(),
                        '$ '.number_format($resource->getMonthlyCost(), 2),
                    ];
                })->toArray(),
            );
            $this->newLine();
        }

        $this->newLine();
        // @formatter:off
        $this->table(
            ['Project', 'Cost per Month (USD)', 'Cost per Quarter (USD)', 'Cost per Year (USD)', 'Resource Count'],
            $projects->map(function (Collection $project) {
                return [
                    $project->get('name'),
                    '$ ' . number_format($project->get('resources')->sum(fn(Resource $resource) => $resource->getMonthlyCost()), 2) . ' / month',
                    '$ ' . number_format($project->get('resources')->sum(fn(Resource $resource) => $resource->getMonthlyCost()), 2) * Carbon::MONTHS_PER_QUARTER . ' / quarter',
                    '$ ' . number_format($project->get('resources')->sum(fn(Resource $resource) => $resource->getMonthlyCost()), 2) * Carbon::MONTHS_PER_YEAR . ' / year',
                    $project->get('resources')->count(),
                ];
            })->toArray()
        );
        // @formatter:on
        $this->line(sprintf('Total: <fg=green;options=bold>$ %s</>', number_format($total, 2)));
        $this->newLine();
    }
}

<?php

namespace App\Commands;

use App\Services\Scraper;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use LaravelZero\Framework\Commands\Command;

class DigitalOceanSyncPrices extends Command
{
    /** @var string  */
    protected $signature = 'sync-prices';

   /** @var string  */
    protected $description = "Sync local prices with Digital Ocean's production prices";

    /**
     * @param  \App\Services\Scraper  $scraper
     */
    public function __construct(
        protected Scraper $scraper
    )
    {
        parent::__construct();

        File::ensureDirectoryExists(base_path('content/prices'));
    }

    /**
     * Execute the console command.
     *
     * @return int
     * @throws \Exception
     */
    public function handle():int
    {
        $this->title("Digital Ocean Sync Prices");

        $cookie = $this->ask("Please enter your DigitalOcean authentication cookie from '_digitalocean2_session_v4'");
        if(is_null($cookie)) {
            $this->error("Please enter a DigitalOcean cookie token");

            return static::FAILURE;
        }

        $this->newLine();
        $this->line("Attempting to Load DigitalOcean Database Prices");
        $this->scraper->digitalOceanDatabasePrices($cookie)
        ->tap(function($prices) {
            if($prices->count() > 0) {
                $this->info("\tWriting out pricing");
                File::put(base_path('content/prices/databases.json'), $prices->toJson());
            } else {
                $this->error("\tFailed to load pricing");
            }
        });

        $this->line('Done');
        return static::SUCCESS;
    }
}

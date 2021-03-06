<?php

namespace App\Services;

use Closure;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\LazyCollection;

class DigitalOcean
{
    /**
     * Base URL of the endpoint
     *
     * @var string
     */
    public static string $base = 'https://api.digitalocean.com';

    /**
     * Version of the API to use
     *
     * @var string
     */
    public static string $version = 'v2';

    /**
     * Query a raw URL
     *
     * @param  string  $token
     * @param  string  $url
     * @return array
     * @throws \Exception
     */
    public function url(string $token, string $url): array
    {
        return Http::withToken($token)
            ->acceptJson()
            ->get($url)
            ->json();
    }

    /**
     * Return a lazy collection of projects from the API
     *
     * @param  string  $token
     * @return \Illuminate\Support\LazyCollection
     */
    public function projects(string $token): LazyCollection
    {
        return $this->lazyCollection(function ($page) use ($token) {
            return Http::withToken($token)
                ->acceptJson()
                ->get("{$this::$base}/{$this::$version}/projects", [
                    'page' => $page
                ])
                ->json('projects', []);
        });
    }

    /**
     * Return a lazy collection of custom images from the API
     *
     * @param  string  $token
     * @return \Illuminate\Support\LazyCollection
     */
    public function customImages(string $token): LazyCollection
    {
        return $this->lazyCollection(function ($page) use ($token) {
            return Http::withToken($token)
                ->acceptJson()
                ->get("{$this::$base}/{$this::$version}/images", [
                    'page' => $page,
                    'private' => 'true',
                ])
                ->json('images', []);
        });
    }

    /**
     * Return a list of resources for a project
     *
     * @param  string  $token
     * @param  string  $project
     * @return \Illuminate\Support\LazyCollection
     */
    public function projectResources(string $token, string $project): LazyCollection
    {
        return $this->lazyCollection(function ($page) use ($token, $project) {
            return Http::withToken($token)
                ->acceptJson()
                ->get("{$this::$base}/{$this::$version}/projects/{$project}/resources", [
                    'page' => $page,
                ])
                ->json('resources', []);
        });
    }

    /**
     * Get a container registry
     *
     * @param  string  $token
     * @return array
     */
    public function containerRegistry(string $token): array
    {
        return Http::withToken($token)
            ->acceptJson()
            ->get("{$this::$base}/{$this::$version}/registry")
            ->json();
    }

    /**
     * Get a snapshot by id
     *
     * @param  string  $token
     * @param  string  $id
     * @return array
     * @throws \Exception
     */
    public function snapshot(string $token, string $id): array
    {
        return Http::withToken($token)
            ->acceptJson()
            ->get("{$this::$base}/{$this::$version}/snapshots/{$id}")
            ->json();
    }

    /**
     * Lazify a given API callback
     *
     * @param  \Closure  $callback
     * @return \Illuminate\Support\LazyCollection
     */
    private function lazyCollection(Closure $callback): LazyCollection
    {
        return LazyCollection::make(function () use ($callback) {
            $page = 1;
            do {
                $items = $callback($page);

                foreach ($items as $item) {
                    yield $item;
                }

                $page += 1;
            } while (count($items));
        });
    }
}

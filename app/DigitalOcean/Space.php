<?php

namespace App\DigitalOcean;

use App\Services\DigitalOcean;

class Space extends Resource
{
    /**
     * @inheritDoc
     */
    public static function FromUrl(string $url, string $token): Resource
    {
        return static::make(
            app(DigitalOcean::class),
            [
                'space' => [
                    'name' => $url,
                ]
            ],
            $token
        );
    }

    /**
     * @inheritDoc
     */
    protected function name(): string
    {
        return $this->data['space']['name'];
    }

    /**
     * @link https://docs.digitalocean.com/products/spaces/#plans-and-pricing
     * @return float
     */
    public function monthlyCost(): float
    {
        return 5.00;
    }
}

<?php

namespace App\DigitalOcean;

class Space extends Resource
{
    /**
     * @return float
     */
    public function getMonthlyCost(): float
    {
        return 5.00;
    }
}

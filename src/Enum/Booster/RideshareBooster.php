<?php
namespace App\Enum\Booster;

enum RideshareBooster
{

case FIVE_RIDESHARES_IN_8_HOURS;

    public function points(): string
    {
        return match($this)
        {
            RideshareBooster::FIVE_RIDESHARES_IN_8_HOURS => 10,
        };
    }
}


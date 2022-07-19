<?php
namespace App\Enum\Booster;

enum DeliveryBooster
{

case FIVE_DELIVERIES_IN_2_HOURS;

    public function points(): string
    {
        return match($this)
        {
            DeliveryBooster::FIVE_DELIVERIES_IN_2_HOURS => 5,
        };
    }
}

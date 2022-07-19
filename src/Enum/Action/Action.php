<?php
namespace App\Enum\Action;

enum Action
{

case DELIVERY;
case RIDESHARE;
case RENT;

    public function points(): string
{
    return match($this)
    {
            Action::DELIVERY => 1,
            Action::RIDESHARE => 1,
            Action::RENT => 2,
        };
    }

}
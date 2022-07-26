<?php
namespace App\Enum\Booster;

enum BoosterActiveInterval: string
{

case MONDAY_START = '12:00';
case TUESDAY_START = '11:00';
case WEDNESDAY_START = '09:00';
case WEDNESDAY_END = '23:00';
case TUESDAY_END = '22:00';
case THURSDAY_START = '08:00';
case THURSDAY_END = '24:00';
case MONDAY_END = '14:00';
case FRIDAY_START = '07:00';
case FRIDAY_END = '23:01';
case SATURDAY_START = '13:00';
case SATURDAY_END = '20:00';
case SUNDAY_START = '19:00';
case SUNDAY_END = '21:00';

}

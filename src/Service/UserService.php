<?php

namespace App\Service;

use DateTime;
use DateInterval;
use App\Repository\UserRepository;
use App\Repository\DeliveryRepository;
use App\Repository\RideshareRepository;
use App\Repository\RentRepository;
use App\Enum\Action\Action;
use App\Enum\Booster\DeliveryBooster;
use App\Enum\Booster\RideshareBooster;
use App\Enum\Booster\BoosterActiveInterval;

class UserService
{

    /**
     * UserService constructor.
     * @param UserRepository $userRepository
     * @param DeliveryRepository $deliveryRepository
     * @param RideshareRepository $rideshareRepository
     * @param RentRepository $rentRepository
     */
    public function __construct(
        public UserRepository $userRepository,
        public DeliveryRepository $deliveryRepository,
        public RideshareRepository $rideshareRepository,
        public RentRepository $rentRepository
    )
    {
    }


    /**
     * Calculates user balance for a certain period.
     * The user balance is the sum of calculateCurrentBalanceFromDeliveries,
     * calculateCurrentBalanceFromRideSharing and calculateCurrentBalanceFromRentals
     * @param int $userId
     * @param string $fromDate
     * @param string $toDate
     * @return int
    */
    public function calculateCurrentBalance(int $userId, string $fromDate, string $toDate): int {

        return $this->calculateCurrentBalanceFromDeliveries($userId, $fromDate, $toDate)
            +
            $this->calculateCurrentBalanceFromRideSharing($userId, $fromDate,$toDate)
            +
            $this->calculateCurrentBalanceFromRentals($userId, $fromDate, $toDate);
    }

    /**
     * Calculates user balance from deliveries for a given period.
     * Current rules are:
     * Every delivery is attributed 1 point.
     * Booster: 5 deliveries in 2 hours result in 5 additional points.
     * Points for actions don’t expire(1 delivery = 1 action).
     * Points from boosters are valid only for one month and then lost unless the user withdraws them.
     * @param int $userId
     * @param string $fromDate
     * @param string $toDate
     * @return int
     * @throws \Exception
    */
    public function calculateCurrentBalanceFromDeliveries(int $userId, string $fromDate, string $toDate): int {

        $deliveries = $this->deliveryRepository->findUserDeliveriesBetweenDatesForWhichPointsWereNotWithdrawn($userId, $fromDate, $toDate);

        $deliveryAction = Action::DELIVERY;
        $fiveDeliveriesIn2HoursBooster = DeliveryBooster::FIVE_DELIVERIES_IN_2_HOURS;


        $total = 0;
        foreach($deliveries as $delivery) {


            $deliveryDate = DateTime::createFromImmutable($delivery->getCreatedAt());

            $lastMonth = (clone $deliveryDate)->sub(new DateInterval('PT1M'));


            // get the day of week
            $dayOfWeekDelivery = strtoupper($deliveryDate->format('l'));
            // build the constant name for booster interval, eg MONDAY_START, MONDAY_END
            $boosterDayStart = $dayOfWeekDelivery . '_START';
            $boosterDayEnd = $dayOfWeekDelivery . '_END';

            // get date 2h before delivery
            $deliveryFromDate = (clone $deliveryDate)->sub(new DateInterval("PT2H"));


            $deliveryFromDateFormatted = $deliveryFromDate->format('Y-m-d');

            // if it's in within 1 month
            if($lastMonth < $deliveryFromDate) {

                $boosterDayStartTime = constant('App\Enum\Booster\BoosterActiveInterval::' . $boosterDayStart);

                // if the delivery time is before the booster start
                if ($deliveryFromDate < new DateTime($deliveryFromDate->format('Y-m-d') . ' ' . $boosterDayStartTime->value)) {
                    // use the booster start time instead
                    $deliveryFromDateFormatted = $deliveryFromDate->format('Y-m-d') . ' ' . $boosterDayStartTime->value;
                }
            }

            // get date 2h after delivery
            $deliveryToDate = (clone $deliveryDate)->add(new DateInterval("PT2H"));

            $deliveryToDateFormatted = $deliveryToDate->format('Y-m-d');

            // if it's in within 1 month
            if($lastMonth < $deliveryToDate) {

                $booterDayEndTime = constant('App\Enum\Booster\BoosterActiveInterval::' . $boosterDayEnd);

                // if the delivery time is after the booster start
                if ($deliveryToDate > new DateTime($deliveryToDate->format('Y-m-d') . ' ' . $booterDayEndTime->value)) {
                    // use the booster end time instead
                    $deliveryToDateFormatted = $deliveryToDate->format('Y-m-d') . ' ' . $booterDayEndTime->value;
                }
            }

            // find all deliveries within 2h interval
            if($this->deliveryRepository->findUserDeliveriesBetweenDatesForWhichPointsWereNotWithdrawn($userId, $deliveryFromDateFormatted, $deliveryToDateFormatted) >= 5) {
                $total += $fiveDeliveriesIn2HoursBooster->points();

            }
                $total += $deliveryAction->points();

            }

        return $total;
    }

    /**
     * Calculates user balance from ride sharing for a given period.
     * Current rules are:
     * Every ride sharing is attributed 1 point.
     * Booster: 5 rideshares in 8 hours result in 10 additional points.
     * Points for actions don’t expire(1 rideshare = 1 action).
     * Points from boosters are valid only for one month and then lost unless the user withdraws them.
     * @param int $userId
     * @param string $fromDate
     * @param string $toDate
     * @return int
     * @throws \Exception
    */
    public function calculateCurrentBalanceFromRideSharing(int $userId, string $fromDate, string $toDate): int {

        $rideSharings = $this->rideshareRepository->findUserRidesharesBetweenDatesForWhichPointsWereNotWidthdrawn($userId, $fromDate, $toDate);

        $rideshareAction = Action::RIDESHARE;
        $fiveRidesharesIn8HoursBooster = RideshareBooster::FIVE_RIDESHARES_IN_8_HOURS;

        $total = 0;
        foreach($rideSharings as $rideSharing) {

            $rideShareDate = DateTime::createFromImmutable($rideSharing->getCreatedAt());

            $lastMonth = (clone $rideShareDate)->sub(new DateInterval('PT1M'));

            // get the day of week
            $dayOfWeekDelivery = strtoupper($rideShareDate->format('l'));

            // build the constant name for booster interval, eg MONDAY_START, MONDAY_END
            $boosterDayStart = $dayOfWeekDelivery . '_START';
            $boosterDayEnd = $dayOfWeekDelivery . '_END';

            // get date 8h before delivery
            $rideShareFromDate = (clone $rideShareDate)->sub(new DateInterval("PT8H"));

            $rideShareFromDateFormatted = $rideShareFromDate->format('Y-m-d H:i:s');

            // if it's in within 1 month
            if($lastMonth < $rideShareFromDate) {

                $boosterDayStartTime = constant('App\Enum\Booster\BoosterActiveInterval::' . $boosterDayStart);

                // if the rideshare time is after the booster start
                if ($rideShareFromDate < new DateTime($rideShareFromDate->format('Y-m-d') . ' ' . $boosterDayStartTime->value)) {
                    // use the booster end time instead
                    $rideShareFromDateFormatted = $rideShareFromDate->format('Y-m-d') . ' ' . $boosterDayStartTime->value;
                }
            }

            // get date 8h after delivery
            $rideShareToDate = (clone $rideShareDate)->add(new DateInterval("PT8H"));

            $rideShareToDateFormatted = $rideShareDate->format('Y-m-d');

            // if it's in within 1 month
            if($lastMonth < $rideShareToDate) {

                $booterDayEndTime = constant('App\Enum\Booster\BoosterActiveInterval::' . $boosterDayEnd);

                // if the delivery time is after the booster start
                if ($rideShareToDate > new DateTime($rideShareToDate->format('Y-m-d') . ' ' . $booterDayEndTime->value)) {
                    // use the booster end time instead
                    $rideShareToDateFormatted = $rideShareToDate->format('Y-m-d') . ' ' . $booterDayEndTime->value;
                }
            }

            // find all deliveries within 8h interval
            if($this->rideshareRepository->findUserRidesharesBetweenDatesForWhichPointsWereNotWidthdrawn($userId, $rideShareFromDateFormatted, $rideShareToDateFormatted) >= 5) {
                $total += $fiveRidesharesIn8HoursBooster->points();
            }

            $total += $rideshareAction->points();

        }

        return $total;
    }

    /**
     * Calculates user balance from rentals for a given period.
     * Current rules are:
     * Every rental is attributed 1 point.
     * Points for actions don’t expire(1 rental = 1 action).
     * @param int $userId
     * @param string $fromDate
     * @param string $toDate
     * @return int
     * @throws \Exception
    */
    public function calculateCurrentBalanceFromRentals(int $userId, string $fromDate, string $toDate): int {

        $rentalsNumber = $this->rentRepository->findBetweenDates($userId, $fromDate, $toDate);

        $rentAction = Action::RIDESHARE;

        $total = 0;
        for($i = 0; $i <= $rentalsNumber; $i++) {

            $total += $rentAction->points();
        }

        return $total;
    }
}


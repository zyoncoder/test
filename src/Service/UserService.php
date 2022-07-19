<?php

namespace App\Service;

use DateTime;
use DateInterval;
use App\Repository\UserRepository;
use App\Repository\DeliveryRepository;
use App\Repository\RideshareRepository;
use App\Repository\RentRepository;

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
     * Calculates user balance for a given period.
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

        $lastMonth = (new Datetime())->sub(new DateInterval('PT1M'))->format('Y-m-d');

        $total = 0;
        foreach($deliveries as $delivery) {


            // get the day of week
            $dayOfWeekDelivery = strtoupper((new Datetime($delivery['created_at']))->format('l'));
            // build the constant name for booster interval, eg MONDAY_START, MONDAY_END
            $boosterDayStart = constant($dayOfWeekDelivery . '_START');
            $boosterDayEnd = constant($dayOfWeekDelivery . '_END');

            // get date 2h before delivery
            $deliveryFromDate = (new Datetime($delivery['created_at']))->add(new DateInterval("PT2H"))->format('Y-m-d H:i:s');

            // if it's in within 1 month
            if($lastMonth < $deliveryFromDate) {

                // if the delivery time is before the booster start
                if ($deliveryFromDate->format('H:i') < BoosterActiveInterval::$boosterDayStart) {
                    // use the booster start time instead
                    $deliveryFromDate = $deliveryFromDate->format('Y-m-d') . ' ' . BoosterActiveInterval::$boosterDayStart;
                }
            }

            // get date 2h after delivery
            $deliveryToDate = (new Datetime($delivery['created_at']))->sub(new DateInterval("PT2H"))->format('Y-m-d H:i:s');

            // if it's in within 1 month
            if($lastMonth < $deliveryToDate) {

                // if the delivery time is after the booster start
                if ($deliveryToDate->format('H:i') > BoosterActiveInterval::$boosterDayEnd) {
                    // use the booster end time instead
                    $deliveryToDate = $deliveryToDate->format('Y-m-d') . ' ' . BoosterActiveInterval::$boosterDayEnd;
                }
            }

            // find all deliveries within 2h interval
            if($this->deliveryRepository->findUserDeliveriesBetweenDatesForWhichPointsWereNotWithdrawn($userId, $deliveryFromDate, $deliveryToDate) >= 5) {
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

        $lastMonth = (new Datetime())->sub(new DateInterval('PT1M'))->format('Y-m-d');

        $total = 0;
        foreach($rideSharings as $rideSharing) {

            // get the day of week
            $dayOfWeekDelivery = strtoupper((new Datetime($rideSharing['created_at']))->format('l'));
            // build the constant name for booster interval, eg MONDAY_START, MONDAY_END
            $boosterDayStart = constant($dayOfWeekDelivery . '_START');
            $boosterDayEnd = constant($dayOfWeekDelivery . '_END');

            // get date 8h before delivery
            $rideShareFromDate = (new Datetime($rideSharing['created_at']))->add(new DateInterval("PT8H"))->format('Y-m-d H:i:s');

            // if it's in within 1 month
            if($lastMonth < $rideShareFromDate) {

                // if the rideshare time is after the booster start
                if ($rideShareFromDate->format('H:i') < BoosterActiveInterval::$boosterDayStart) {
                    // use the booster end time instead
                    $rideShareFromDate = $rideShareFromDate->format('Y-m-d') . ' ' . BoosterActiveInterval::$boosterDayStart;
                }
            }

            // get date 8h after delivery
            $rideShareToDate = (new Datetime($rideSharing['created_at']))->sub(new DateInterval("PT8H"))->format('Y-m-d H:i:s');

            // if it's in within 1 month
            if($lastMonth < $rideShareToDate) {

                // if the delivery time is after the booster start
                if ($rideShareToDate->format('H:i') > BoosterActiveInterval::$boosterDayEnd) {
                    // use the booster end time instead
                    $rideShareToDate = $rideShareToDate->format('Y-m-d') . ' ' . BoosterActiveInterval::$boosterDayEnd;
                }
            }

            // find all deliveries within 8h interval
            if($this->rideshareRepository->findUserRidesharesBetweenDatesForWhichPointsWereNotWidthdrawn($userId, $rideShareFromDate, $rideShareToDate) >= 5) {
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


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

        //return $this->calculateCurrentBalanceFromDeliveries($userId, $fromDate, $toDate);
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

        $now = new DateTime();
        $deliveriesUsedInBooster = [];
        $total = 0;
        foreach($deliveries as $delivery) {
            $deliveryDate = DateTime::createFromImmutable($delivery->getCreatedAt());

            // get the delivery day of week
            $dayOfWeekDelivery = strtoupper($deliveryDate->format('l'));
            
            // build the constant name for booster interval, eg MONDAY_START, MONDAY_END
            $boosterDayStart = $dayOfWeekDelivery . '_START';
            $boosterDayEnd = $dayOfWeekDelivery . '_END';

            $boosterDayStartTime = constant('App\Enum\Booster\BoosterActiveInterval::' . $boosterDayStart);
            $booterDayEndTime = constant('App\Enum\Booster\BoosterActiveInterval::' . $boosterDayEnd);

            // if it qualifies for booster
            if($deliveryDate > new DateTime($deliveryDate->format('Y-m-d') . ' ' . $boosterDayStartTime->value)
            && $deliveryDate < new DateTime($deliveryDate->format('Y-m-d') . ' ' . $booterDayEndTime->value)) {

                // get date 2h before delivery
                $deliveryFromDate = (clone $deliveryDate)->sub(new DateInterval("PT2H"));

                $deliveryFromDateFormatted = $deliveryFromDate->format('Y-m-d H:i:s');

                $timeIntervalBetweenDeliveryDateMinus2HAndToday = $now->diff($deliveryFromDate);

                // if delivery date is in the last month
                if ($timeIntervalBetweenDeliveryDateMinus2HAndToday->y === 0 && $timeIntervalBetweenDeliveryDateMinus2HAndToday->m === 0) {

                    // if the delivery time is before the booster start
                    if ($deliveryFromDate < new DateTime($deliveryFromDate->format('Y-m-d') . ' ' . $boosterDayStartTime->value)) {
                        // use the booster start time instead
                        $deliveryFromDateFormatted = $deliveryFromDate->format('Y-m-d') . ' ' . $boosterDayStartTime->value;
                    }
                }

                // get date 2h after delivery
                $deliveryToDate = (clone $deliveryDate)->add(new DateInterval("PT2H"));

                $deliveryToDateFormatted = $deliveryToDate->format('Y-m-d H:i:s');

                $timeIntervalBetweenDeliveryDatePlus2HoursAndToday = $now->diff($deliveryFromDate);

                // if it's in within 1 month
                if ($timeIntervalBetweenDeliveryDatePlus2HoursAndToday->y === 0 && $timeIntervalBetweenDeliveryDatePlus2HoursAndToday->m === 0) {

                    // if the delivery time is after the booster end
                    if ($deliveryToDate > new DateTime($deliveryToDate->format('Y-m-d') . ' ' . $booterDayEndTime->value)) {
                        // use the booster end time instead
                        $deliveryToDateFormatted = $deliveryToDate->format('Y-m-d') . ' ' . $booterDayEndTime->value;
                    }
                }


                $deliveriesIn2hInterval = $this->deliveryRepository->findUserDeliveriesBetweenDatesForWhichPointsWereNotWithdrawnAndBoosterWasNotUsed($userId, $deliveryFromDateFormatted, $deliveryToDateFormatted, $deliveriesUsedInBooster);

                // find all deliveries within 2h interval
                if (count($deliveriesIn2hInterval) >= 5) {
                    $total += $fiveDeliveriesIn2HoursBooster->points();

                    $deliveriesUsedInBooster[] = $delivery->getId();

                    $deliveriesUsedInBooster = array_merge($deliveriesUsedInBooster, array_column($deliveriesIn2hInterval, 'id'));
                }
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

        $now = new DateTime();
        $rideSharesUsedInBooster = [];

        $total = 0;
        foreach($rideSharings as $rideSharing) {

            $rideShareDate = DateTime::createFromImmutable($rideSharing->getCreatedAt());

            // get the day of week
            $dayOfWeekDelivery = strtoupper($rideShareDate->format('l'));

            // build the constant name for booster interval, eg MONDAY_START, MONDAY_END
            $boosterDayStart = $dayOfWeekDelivery . '_START';
            $boosterDayEnd = $dayOfWeekDelivery . '_END';

            $boosterDayStartTime = constant('App\Enum\Booster\BoosterActiveInterval::' . $boosterDayStart);
            $booterDayEndTime = constant('App\Enum\Booster\BoosterActiveInterval::' . $boosterDayEnd);


            // if it qualifies for booster
            if($rideShareDate > new DateTime($rideShareDate->format('Y-m-d') . ' ' . $boosterDayStartTime->value)
                &&  $rideShareDate < new DateTime($rideShareDate->format('Y-m-d') . ' ' . $booterDayEndTime->value)) {


                // get date 8h before delivery
                $rideShareFromDate = (clone $rideShareDate)->sub(new DateInterval("PT8H"));

                $rideShareFromDateFormatted = $rideShareFromDate->format('Y-m-d H:i:s');

                $timeIntervalBetweenRideShareDateMinus8HAndToday = $now->diff($rideShareFromDate);

                // if it's in within 1 month
                if ($timeIntervalBetweenRideShareDateMinus8HAndToday->y === 0 && $timeIntervalBetweenRideShareDateMinus8HAndToday->m === 0) {

                    // if the rideshare time is after the booster start
                    if ($rideShareFromDate < new DateTime($rideShareFromDate->format('Y-m-d') . ' ' . $boosterDayStartTime->value)) {
                        // use the booster end time instead
                        $rideShareFromDateFormatted = $rideShareFromDate->format('Y-m-d') . ' ' . $boosterDayStartTime->value;
                    }
                }

                // get date 8h after delivery
                $rideShareToDate = (clone $rideShareDate)->add(new DateInterval("PT8H"));

                $rideShareToDateFormatted = $rideShareDate->format('Y-m-d H:i:s');

                $timeIntervalBetweenDeliveryDatePlus8HoursAndToday = $now->diff($rideShareToDate);

                // if it's in within 1 month
                if ($timeIntervalBetweenDeliveryDatePlus8HoursAndToday->y === 0 && $timeIntervalBetweenDeliveryDatePlus8HoursAndToday->m === 0) {

                    // if the delivery time is after the booster start
                    if ($rideShareToDate > new DateTime($rideShareToDate->format('Y-m-d') . ' ' . $booterDayEndTime->value)) {
                        // use the booster end time instead
                        $rideShareToDateFormatted = $rideShareToDate->format('Y-m-d') . ' ' . $booterDayEndTime->value;
                    }
                }
                
                $rideSharesIn8hInterval = $this->rideshareRepository->findUserRidesharesInTimeIntervalForWhichPointsWereNotWidthdrawnAndBoosterWasNotUsed($userId, $rideShareFromDateFormatted, $rideShareToDateFormatted, $rideSharesUsedInBooster);

                // find all ride shares within 8h interval
                if (count($rideSharesIn8hInterval) >= 5) {
                    $total += $fiveRidesharesIn8HoursBooster->points();

                    $rideSharesUsedInBooster[] = $rideSharing->getId();

                    $rideSharesUsedInBooster = array_merge($rideSharesUsedInBooster, array_column($rideSharesIn8hInterval, 'id'));
                }
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

        $rentalsNumber = $this->rentRepository->findUserRentalsInATimeIntervalForWhichPointsWereNotWidthdraw($userId, $fromDate, $toDate);

        $rentAction = Action::RIDESHARE;

        return $rentalsNumber * $rentAction->points();
    }
}


<?php

namespace App\DataFixtures;

use App\Entity\Rent;
use App\Entity\Rideshare;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class FiveRideShareIn8Hours extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {

        $now = new \DateTimeImmutable();

        for ($i = 1; $i <= 5; $i++) {

            $rideshare = new Rideshare();


            $rideshare->setUser($this->getReference(UserFixtures::USER_REFERENCE));

            $rideshare->setActionPointWithdrew(0);
            $rideshare->setBoosterPointWithdrew(0);
            if($i === 1) {
                $rideshare->setcreatedAt($now);
            }
            else {
                $rideshare->setcreatedAt($now->modify('+' . $i . ' hours'));
            }
            $manager->persist($rideshare);

        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class,
        ];
    }
}

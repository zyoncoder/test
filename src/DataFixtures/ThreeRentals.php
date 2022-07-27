<?php

namespace App\DataFixtures;

use App\Entity\Rent;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ThreeRentals extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {

        $now = new \DateTimeImmutable();

        for ($i = 1; $i <= 3; $i++) {

            $rent = new Rent();


            $rent->setUser($this->getReference(UserFixtures::USER_REFERENCE));

            $rent->setActionPointWithdrew(0);
            $rent->setcreatedAt($now);

            $manager->persist($rent);

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

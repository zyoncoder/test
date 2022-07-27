<?php

namespace App\DataFixtures;

use App\Entity\Rent;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class FiveRentalsIn5Days extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {

        $now = new \DateTimeImmutable();

        for ($i = 1; $i <= 5; $i++) {

            $rent = new Rent();


            $rent->setUser($this->getReference(UserFixtures::USER_REFERENCE));

            $rent->setActionPointWithdrew(0);
            if($i === 1) {
                $rent->setcreatedAt($now);
            }
            else {
                $rent->setcreatedAt($now->modify('+' . ($i - 1) . ' day'));
            }
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

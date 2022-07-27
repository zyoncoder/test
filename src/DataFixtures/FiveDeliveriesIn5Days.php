<?php

namespace App\DataFixtures;

use App\Entity\Delivery;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class FiveDeliveriesIn5Days extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {

        $now = new \DateTimeImmutable();

        for ($i = 1; $i <= 5; $i++) {

            $delivery = new Delivery();


            $delivery->setUser($this->getReference(UserFixtures::USER_REFERENCE));

            $delivery->setActionPointWithdrew(0);
            $delivery->setBoosterPointWithdrew(0);
            if($i === 1) {
                $delivery->setcreatedAt($now);
            }
            else {
                $delivery->setcreatedAt($now->modify('+' . ($i - 1) . ' day'));
            }
            $manager->persist($delivery);

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

<?php

namespace App\Tests\Unit;

use App\Service\UserService;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testSomething(): void
    {

        $userServiceMock = $this->createMock(UserService::class);
           // ->disableOriginalConstructor()
            //->onlyMethods(['calculateCurrentBalance', 'calculateCurrentBalanceFromDeliveries', 'calculateCurrentBalanceFromRideSharing', 'calculateCurrentBalanceFromRentals'])
            //->getMock();

        $userServiceMock->method("calculateCurrentBalanceFromDeliveries")->willReturn(1);
        $userServiceMock->method("calculateCurrentBalanceFromRideSharing")->willReturn(1);
        $userServiceMock->method("calculateCurrentBalanceFromRentals")->willReturn(1);


        $this->assertEquals(3, $userServiceMock->calculateCurrentBalance(1, '2022-01-01', '2022-02-02'));
    }
}


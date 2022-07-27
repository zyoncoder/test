<?php

namespace App\Tests\Unit;

use App\DataFixtures\FiveDeliveriesIn2Hours;
use App\DataFixtures\FiveDeliveriesIn5Days;
use App\DataFixtures\FiveRentalsIn5Days;
use App\DataFixtures\FiveRideShareIn5Days;
use App\DataFixtures\FiveRideShareIn8Hours;
use App\DataFixtures\FiveRideSharesIn8Hours;
use App\DataFixtures\ThreeRentals;
use App\DataFixtures\SevenDeliveriesIn2Hours;
use App\DataFixtures\ThreeRideShareIn4Hours;
use App\DataFixtures\UserFixtures;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use GuzzleHttp\Client;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserTest extends WebTestCase
{

protected  $databaseTool;

    private Client $client;

    public function setUp(): void
    {
        parent::setUp();

        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();

        $this->truncateEntities();

        $this->databaseTool->loadFixtures([
            UserFixtures::class
        ]);

        $this->client = new Client(['base_uri' => 'http://127.0.0.1:8000']);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        $this->truncateEntities();
    }


    public function testApiReturns200HttpCode(): void
    {

        $now = new \DateTime();

        $response = $this->client->get('/api/user/1/balance/' . $now->format('Y-m-d H:i:s') . '/' . $now->format('Y-m-d H:i:s'));

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testApiResponseIsJson(): void
    {

        $now = new \DateTime();

        $response = $this->client->get('/api/user/1/balance/' . $now->format('Y-m-d H:i:s') . '/' . $now->format('Y-m-d H:i:s'));

        $this->assertIsArray(json_decode($response->getBody(), true));

    }

    public function testApiResponseHasBalanceKey(): void
    {

        $now = new \DateTime();

        $response = $this->client->get('/api/user/1/balance/' . $now->format('Y-m-d H:i:s') . '/' . $now->format('Y-m-d H:i:s'));

        $data = json_decode($response->getBody(true), true);
        $this->assertArrayHasKey('balance', $data);
    }

    public function testApiResponseUserBalanceValue5DeliveriesIn5DifferentDays(): void
    {

        $this->databaseTool->loadFixtures([
            FiveDeliveriesIn5Days::class
        ]);


        $now = new \DateTime();

        $response = $this->client->get('/api/user/1/balance/' . $now->format('Y-m-d H:i:s') .
            '/' . $now->modify('+5 days')->format('Y-m-d H:i:s'));

        $data = json_decode($response->getBody(true), true);

        $this->assertEquals(5, $data['balance']);
    }

    public function testApiResponseUserBalanceValue5RentalsIn5DifferentDays(): void
    {

        $this->databaseTool->loadFixtures([
            FiveRentalsIn5Days::class
        ]);

        $now = new \DateTime();

        $response = $this->client->get('/api/user/1/balance/' . $now->format('Y-m-d H:i:s') .
            '/' . $now->modify('+5 days')->format('Y-m-d H:i:s'));

        $data = json_decode($response->getBody(true), true);

        $this->assertEquals(5, $data['balance']);
    }

    public function testApiResponseUserBalanceValue5RidesharesIn5DifferentDays(): void
    {

        $this->databaseTool->loadFixtures([
            FiveRideShareIn5Days::class
        ]);

        $now = new \DateTime();

        $response = $this->client->get('/api/user/1/balance/' . $now->format('Y-m-d H:i:s') .
            '/' . $now->modify('+5 days')->format('Y-m-d H:i:s'));

        $data = json_decode($response->getBody(true), true);

        $this->assertEquals(5, $data['balance']);
    }


    public function testApiResponseUserBalanceValue5DeliveriesRidesharesAndRentalsIn5DifferentDays(): void
    {

        $this->databaseTool->loadFixtures([
            FiveDeliveriesIn5Days::class,
            FiveRideShareIn5Days::class,
            FiveRentalsIn5Days::class
        ]);

        $now = new \DateTime();

        $response = $this->client->get('/api/user/1/balance/' . $now->format('Y-m-d H:i:s') .
            '/' . $now->modify('+5 days')->format('Y-m-d H:i:s'));

        $data = json_decode($response->getBody(true), true);

        $this->assertEquals(15, $data['balance']);
    }

    public function testApiResponseUserBalanceValue5DeliveriesIn2Hours(): void
    {

        $this->databaseTool->loadFixtures([
            FiveDeliveriesIn2Hours::class,
        ]);

        $now = new \DateTime();

        $response = $this->client->get('/api/user/1/balance/' . $now->format('Y-m-d H:i:s') .
            '/' . $now->modify('+5 hours')->format('Y-m-d H:i:s'));

        $data = json_decode($response->getBody(true), true);

        $this->assertEquals(10, $data['balance']);
    }

    public function testApiResponseUserBalanceValue5RidesharesIn8Hours(): void
    {

        $this->databaseTool->loadFixtures([
            FiveRideShareIn8Hours::class,
        ]);

        $now = new \DateTime();

        $response = $this->client->get('/api/user/1/balance/' . $now->format('Y-m-d H:i:s') .
            '/' . $now->modify('+8 hours')->format('Y-m-d H:i:s'));

        $data = json_decode($response->getBody(true), true);

        $this->assertEquals(15, $data['balance']);
    }

    public function testApiResponseUserBalanceValue5DeliveriesIn2Hours5RidesharesIn8Hours(): void
    {

        $this->databaseTool->loadFixtures([
            FiveDeliveriesIn2Hours::class,
            FiveRideShareIn8Hours::class,
        ]);

        $now = new \DateTime();

        $response = $this->client->get('/api/user/1/balance/' . $now->format('Y-m-d H:i:s') .
            '/' . $now->modify('+8 hours')->format('Y-m-d H:i:s'));

        $data = json_decode($response->getBody(true), true);

        $this->assertEquals(25, $data['balance']);
    }

    public function testApiResponseUserBalanceValue5DeliveriesIn2Hours5RidesharesIn8Hours5RentalsIn5Days(): void
    {

        $this->databaseTool->loadFixtures([
            FiveDeliveriesIn2Hours::class,
            FiveRideShareIn8Hours::class,
            FiveRentalsIn5Days::class,
        ]);

        $now = new \DateTime();

        $response = $this->client->get('/api/user/1/balance/' . $now->format('Y-m-d H:i:s') .
            '/' . $now->modify('+5 days')->format('Y-m-d H:i:s'));

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody(true), true);
        $this->assertArrayHasKey('balance', $data);

        $this->assertEquals(30, $data['balance']);
    }

    public function testApiResponseUserBalanceValue5DeliveriesIn2Hours5RidesharesIn8Hours5DeliveriesIn5Days5Ridesharesin5Days5RentalsIn5Days(): void
    {

        $this->databaseTool->loadFixtures([
            FiveDeliveriesIn5Days::class,
            FiveRideShareIn5Days::class,
            FiveDeliveriesIn2Hours::class,
            FiveRideShareIn8Hours::class,
            FiveRentalsIn5Days::class,
        ]);

        $now = new \DateTime();

        $response = $this->client->get('/api/user/1/balance/' . $now->format('Y-m-d H:i:s') .
            '/' . $now->modify('+5 days')->format('Y-m-d H:i:s'));

        $data = json_decode($response->getBody(true), true);

        $this->assertEquals(40, $data['balance']);
    }

    public function testApiResponseUserBalanceValue7DeliveriesIn2Hours3RidesharesIn4Hours3Rentals(): void
    {

        $this->databaseTool->loadFixtures([
            SevenDeliveriesIn2Hours::class,
            ThreeRideShareIn4Hours::class,
            ThreeRentals::class,
        ]);

        $now = new \DateTime();

        $response = $this->client->get('/api/user/1/balance/' . $now->format('Y-m-d H:i:s') .
            '/' . $now->modify('+5 days')->format('Y-m-d H:i:s'));

        $data = json_decode($response->getBody(true), true);

        $this->assertEquals(18, $data['balance']);
    }

    private function truncateEntities()
    {
        $purger = new ORMPurger($this->getEntityManager());
        $purger->purge();
    }

    private function getEntityManager()
    {
        return self::getContainer()
            ->get('doctrine')
            ->getManager();
    }
}


<?php

namespace App\Tests\Unit;

use App\DataFixtures\FiveDeliveriesIn5Days;
use App\DataFixtures\FiveRentalsIn5Days;
use App\DataFixtures\FiveRideShareIn5Days;
use App\DataFixtures\UserFixtures;
use App\Entity\Delivery;
use App\Entity\Rent;
use App\Entity\Rideshare;
use App\Entity\User;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use GuzzleHttp\Client;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserTest extends WebTestCase
{

protected  $databaseTool;


    public function setUp(): void
    {
        parent::setUp();

        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();

        $this->truncateEntities();

        $this->databaseTool->loadFixtures([
            UserFixtures::class
        ]);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        $this->truncateEntities();

    }
    
    public function testApiReturns200HttpCode(): void
    {

        // create our http client (Guzzle)
        $client = new Client(['base_uri' => 'http://127.0.0.1:8000']);

        $response = $client->get('/api/user/1/balance?from=2022-01-01&to=2022-01-02');

        $this->assertEquals(200, $response->getStatusCode());

    }

    public function testApiResponseIsJson(): void
    {

        // create our http client (Guzzle)
        $client = new Client(['base_uri' => 'http://127.0.0.1:8000']);

        $response = $client->get('/api/user/1/balance?from=2022-01-01&to=2022-01-02');

        $this->assertIsArray(json_decode($response->getBody(), true));

    }

    public function testApiResponseHasBalanceKey(): void
    {

        // create our http client (Guzzle)
        $client = new Client(['base_uri' => 'http://127.0.0.1:8000']);

        $response = $client->get('/api/user/1/balance?from=&to=');

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody(true), true);
        $this->assertArrayHasKey('balance', $data);
    }

    public function testApiResponseUserBalanceValue5DeliveriesIn5DifferentDays(): void
    {

        $this->databaseTool->loadFixtures([
            FiveDeliveriesIn5Days::class
        ]);

        // create our http client (Guzzle)
        $client = new Client(['base_uri' => 'http://127.0.0.1:8000']);

        $now = new \DateTime();

        $response = $client->get('/api/user/1/balance?from=' . $now->format('Y-m-d H:i:s') .
            '&to=' . $now->modify('+5 days')->format('Y-m-d H:i:s'));

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody(true), true);
        $this->assertArrayHasKey('balance', $data);

        $this->assertEquals(5, $data['balance']);
    }

    public function testApiResponseUserBalanceValue5RentalsIn5DifferentDays(): void
    {

        $this->databaseTool->loadFixtures([
            FiveRentalsIn5Days::class
        ]);

        // create our http client (Guzzle)
        $client = new Client(['base_uri' => 'http://127.0.0.1:8000']);

        $now = new \DateTime();

        $response = $client->get('/api/user/1/balance?from=' . $now->format('Y-m-d H:i:s') .
            '&to=' . $now->modify('+5 days')->format('Y-m-d H:i:s'));

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody(true), true);
        $this->assertArrayHasKey('balance', $data);

        $this->assertEquals(5, $data['balance']);
    }

    public function testApiResponseUserBalanceValue5RidesharesIn5DifferentDays(): void
    {

        $this->databaseTool->loadFixtures([
            FiveRideShareIn5Days::class
        ]);

        // create our http client (Guzzle)
        $client = new Client(['base_uri' => 'http://127.0.0.1:8000']);

        $now = new \DateTime();

        $response = $client->get('/api/user/1/balance?from=' . $now->format('Y-m-d H:i:s') .
            '&to=' . $now->modify('+5 days')->format('Y-m-d H:i:s'));

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody(true), true);
        $this->assertArrayHasKey('balance', $data);

        $this->assertEquals(5, $data['balance']);
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


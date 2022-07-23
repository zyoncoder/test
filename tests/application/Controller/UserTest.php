<?php

namespace App\Tests\Unit;

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{

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

        $response = $client->get('/api/user/1/balance?from=2022-01-01&to=2022-01-02');

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody(true), true);
        $this->assertArrayHasKey('balance', $data);

        $this->assertEquals(26, $data['balance']);
    }

    public function testApiResponseUserBalanceValue(): void
    {

        // create our http client (Guzzle)
        $client = new Client(['base_uri' => 'http://127.0.0.1:8000']);

        $response = $client->get('/api/user/1/balance?from=2022-01-01&to=2022-01-02');

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody(true), true);
        $this->assertArrayHasKey('balance', $data);

        $this->assertEquals(26, $data['balance']);
    }
}


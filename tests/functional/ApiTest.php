<?php

/**
 *
 */
class ApiTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var
     */
    protected $client;

    /**
     *
     */
    protected function setUp()
    {
        $this->client = new GuzzleHttp\Client([
            'base_url' => 'http://localhost:8888',
            'defaults' => [
                'exceptions' => false,
            ],
        ]);
    }

    /**
     *
     */
    public function testPingSuccess()
    {
        $response = $this->client->request('GET', 'http://localhost:8888/ext/modules/expressly/dispatcher.php?query=/expressly/api/ping');

        $this->assertEquals(200, $response->getStatusCode());
        // TODO: needs to be "application/json"
        $this->assertEquals('text/html', $response->getHeaderLine('Content-Type'));

        $this->assertJson(strval($response->getBody()));

        $this->assertEquals(['expressly' => 'Stuff is happening!'], json_decode($response->getBody(), true));
    }

    /**
     *
     */
    public function testUserSuccess()
    {
        $response = $this->client->request('GET', 'http://localhost:8888/ext/modules/expressly/dispatcher.php?query=/expressly/api/user/test@test.com', [
            'http_errors' => false,
            //'auth'        => ''
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        // TODO: needs to be "application/json"
        $this->assertEquals('text/html', $response->getHeaderLine('Content-Type'));

        $this->assertJson(strval($response->getBody()));

        // Do something
    }

    /**
     *
     */
    public function testUserFailed()
    {
        $response = $this->client->request('GET', 'http://localhost:8888/ext/modules/expressly/dispatcher.php?query=/expressly/api/user/test@test.com', ['http_errors' => false]);

        $this->assertEquals(401, $response->getStatusCode());
    }


}

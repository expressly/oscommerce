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
        $this->client = new \Buzz\Client\Curl();
    }

    /**
     *
     */
    public function testPingSuccess()
    {
        $request  = new Buzz\Message\Request('GET', '/ext/modules/expressly/dispatcher.php?query=/expressly/api/ping', 'http://localhost:8888');
        $response = new Buzz\Message\Response();

        $this->client->send($request, $response);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeader('Content-Type'));

        $this->assertJson(strval($response->getContent()));

        $this->assertEquals(['expressly' => 'Stuff is happening!'], json_decode($response->getContent(), true));
    }

    /**
     *
     */
    public function testUserSuccess()
    {
        $request  = new Buzz\Message\Request('GET', '/ext/modules/expressly/dispatcher.php?query=/expressly/api/user/test1234567890@test.com', 'http://localhost:8888');
        $request->addHeader('Authorization: Basic ODI4MjdhMjUtYWZmOS00NjRlLTkwYzUtODNjNDUxMTdkYmJkOlFrRDV6T0NPS21XUUhUVGxrV0Zvb25UVUUwQ0xsb1ZY');
        $response = new Buzz\Message\Response();

        $this->client->send($request, $response);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeader('Content-Type'));

        $this->assertJson(strval($response->getContent()));

        // Do something else
    }

    /**
     *
     */
    public function testUserFailed()
    {
        $request  = new Buzz\Message\Request('GET', '/ext/modules/expressly/dispatcher.php?query=/expressly/api/user/test1234567890@test.com', 'http://localhost:8888');
        $response = new Buzz\Message\Response();

        $this->client->send($request, $response);

        $this->assertEquals(401, $response->getStatusCode());
    }

}

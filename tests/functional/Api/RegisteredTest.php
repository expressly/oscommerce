<?php

require_once('BaseApiTestCase.php');

/**
 *
 */
class RegisteredTest extends BaseApiTestCase
{
    /**
     *
     */
    public function testSuccess()
    {
        $request  = new Buzz\Message\Request('GET', '/ext/modules/expressly/dispatcher.php?query=/expressly/api/registered', $this->getDefaultHost());
        $request->addHeader('Authorization: Basic '.OSCOM_APP_EXPRESSLY_APIKEY);
        $response = new Buzz\Message\Response();

        $this->client->send($request, $response);

        //////////

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeader('Content-Type'));

        $this->assertJson(strval($response->getContent()));

        $this->assertEquals([
            'registered' => true
        ], json_decode($response->getContent(), true));
    }
    /**
     *
     */
    public function testFailed()
    {
        $request  = new Buzz\Message\Request('GET', '/ext/modules/expressly/dispatcher.php?query=/expressly/api/registered', $this->getDefaultHost());
        $response = new Buzz\Message\Response();

        $this->client->send($request, $response);

        //////////

        $this->assertEquals(401, $response->getStatusCode());
    }
}

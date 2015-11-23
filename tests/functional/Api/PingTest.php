<?php

require_once('BaseApiTestCase.php');

/**
 *
 */
class PingTest extends BaseApiTestCase
{
    /**
     *
     */
    public function testPingSuccess()
    {
        $request  = new Buzz\Message\Request('GET', '/ext/modules/expressly/dispatcher.php?query=/expressly/api/ping', $this->getDefaultHost());
        $response = new Buzz\Message\Response();

        $this->client->send($request, $response);

        //////////

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeader('Content-Type'));

        $this->assertJson(strval($response->getContent()));

        $this->assertEquals([
            'expressly' => 'Stuff is happening!'
        ], json_decode($response->getContent(), true));
    }
}

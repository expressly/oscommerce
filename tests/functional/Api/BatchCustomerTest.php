<?php

require_once('BaseApiTestCase.php');

/**
 *
 */
class BatchCustomerTest extends BaseApiTestCase
{
    /**
     *
     */
    public function testSuccess()
    {
        $request  = new Buzz\Message\Request('POST', '/ext/modules/expressly/dispatcher.php?query=/expressly/api/batch/customer', $this->getDefaultHost());
        $request->addHeader('Authorization: Basic '.OSCOM_APP_EXPRESSLY_APIKEY);
        $request->setContent(json_encode([
            'emails' => ['john.doe@example.com', 'jane.doe@example.com'],
        ]));
        $response = new Buzz\Message\Response();

        $this->client->send($request, $response);

        //////////

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeader('Content-Type'));

        $this->assertJson(strval($response->getContent()));

        $this->assertEquals([
            'existing' => ['john.doe@example.com', 'jane.doe@example.com'],
            'deleted'  => [],
            'pending'  => []
        ], json_decode($response->getContent(), true));
    }

    /**
     *
     */
    public function testFailed()
    {
        $request  = new Buzz\Message\Request('POST', '/ext/modules/expressly/dispatcher.php?query=/expressly/api/batch/customer', $this->getDefaultHost());
        $response = new Buzz\Message\Response();

        $this->client->send($request, $response);

        //////////

        $this->assertEquals(401, $response->getStatusCode());
    }
}

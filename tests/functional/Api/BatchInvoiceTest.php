<?php

require_once('BaseApiTestCase.php');

/**
 *
 */
class BatchInvoiceTest extends BaseApiTestCase
{
    /**
     *
     */
    public function testSuccess()
    {
        $request  = new Buzz\Message\Request('POST', '/ext/modules/expressly/dispatcher.php?query=/expressly/api/batch/invoice', $this->getDefaultHost());
        $request->addHeader('Authorization: Basic '.OSCOM_APP_EXPRESSLY_APIKEY);

        $request->setContent(json_encode([
            'customers' => [
                [
                    'email' => 'john.doe@example.com',
                    'from'  => '2015-01-01 00:00:00',
                    'to'    => '2016-01-01 00:00:00',
                ]
            ],
        ]));
        $response = new Buzz\Message\Response();

        $this->client->send($request, $response);

        //////////

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeader('Content-Type'));

        $this->assertJson(strval($response->getContent()));

        /**
         * Check predefined data in tests/fixtures/dump.sql
         */
        $this->assertEquals([
            'invoices' => [
                [
                    'email'        => 'john.doe@example.com',
                    'orderCount'   => 1,
                    'preTaxTotal'  => 529.98,
                    'postTaxTotal' => 529.98,
                    'tax'          => 0,
                    'orders' => [
                        [
                            'id'           => 1,
                            'date'         => '2015-10-13T13:48:53+0000',
                            'preTaxTotal'  => 529.98,
                            'postTaxTotal' => 529.98,
                            'tax'          => 0
                        ]
                    ]
                ]
            ]
        ], json_decode(strval($response->getContent()), true));
    }

    /**
     *
     */
    public function testFailed()
    {
        $request  = new Buzz\Message\Request('POST', '/ext/modules/expressly/dispatcher.php?query=/expressly/api/batch/invoice', $this->getDefaultHost());
        $response = new Buzz\Message\Response();

        $this->client->send($request, $response);

        //////////

        $this->assertEquals(401, $response->getStatusCode());
    }
}

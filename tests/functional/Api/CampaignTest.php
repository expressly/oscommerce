<?php

require_once('BaseApiTestCase.php');

/**
 *
 */
class CampaignTest extends BaseApiTestCase
{
    /**
     * Test UUID value
     */
    const UUID = 'a11f74d1-98f8-482b-98be-bc197c7cca37';

    /**
     *
     */
    public function testPopupSuccess()
    {
        $request  = new Buzz\Message\Request('GET', '/ext/modules/expressly/dispatcher.php?query=/expressly/api/'.self::UUID, $this->getDefaultHost());
        $response = new Buzz\Message\Response();

        $this->client->send($request, $response, [
            CURLOPT_FOLLOWLOCATION => 0,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HEADER         => 1
        ]);

        //////////

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/ext/modules/expressly/popup.php?uuid='.self::UUID, $response->getHeader('Location'));
    }

    /**
     *
     */
    public function testMigrateSuccess()
    {
        $request  = new Buzz\Message\Request('GET', '/ext/modules/expressly/dispatcher.php?query=/expressly/api/'.self::UUID.'/migrate', $this->getDefaultHost());
        $response = new Buzz\Message\Response();

        $this->client->send($request, $response, [
            CURLOPT_FOLLOWLOCATION => 0,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HEADER         => 1
        ]);

        //////////

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/ext/modules/expressly/migrate.php?uuid='.self::UUID, $response->getHeader('Location'));
    }
}

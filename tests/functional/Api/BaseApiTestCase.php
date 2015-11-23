<?php

/**
 *
 */
abstract class BaseApiTestCase extends PHPUnit_Framework_TestCase
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
     * @return string
     */
    public function getDefaultHost()
    {
        return 'http://' . WEB_SERVER_HOST . ':' . WEB_SERVER_PORT;
    }
}

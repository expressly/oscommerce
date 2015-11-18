<?php

/**
 *
 */
class PingTest extends PHPUnit_Framework_TestCase
{
    public function testEnvironment()
    {
        // TODO: Check is localhost:8888 environment available
        // {"expressly":"Stuff is happening!"}
        $response       = file_get_contents('http://localhost:8888/ext/modules/expressly/dispatcher.php?query=/expressly/api/ping');
        $response_array = !empty($response) ? json_decode($response) : new stdClass();

        $this->assertObjectHasAttribute('expressly', $response_array);
    }
}

<?php

require_once dirname(dirname(__DIR__)) . '/catalog/includes/apps/expressly/OSCOM_Expressly.php';

/**
 *
 */
class ApiUnitTest extends PHPUnit_Framework_TestCase
{
    public function testPing()
    {
        $OSCOM_Expressly = new \OSCOM_Expressly();
        $OSCOM_Expressly->runDispatcher('/expressly/api/ping');
    }
}

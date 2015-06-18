<?php

use Expressly\Event\CustomerMigrateEvent;
use Expressly\Exception\ExceptionFormatter;
use Expressly\Exception\GenericException;
use Expressly\Lib\Customer;

chdir('../../../');
require 'includes/apps/expressly/expressly.php';

try {
    if (empty($_GET['uuid'])) {
        throw new GenericException('Invalid uuid');
    }

    $uuid = $_GET['uuid'];
    $event = new CustomerMigrateEvent($merchant, $uuid);
    $dispatcher->dispatch('customer.migrate.data', $event);

    $content = $event->getContent();
    if (!$event->isSuccessful()) {
        throw new GenericException($content['message']);
    }

    $osCustomer = new Customer($app);
    $osCustomer->add($uuid, $content);
} catch (\Exception $e) {
    $logger->addError(ExceptionFormatter::format($e));
}

tep_redirect('/');
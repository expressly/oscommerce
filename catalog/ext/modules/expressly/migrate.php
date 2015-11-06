<?php

use Expressly\Event\CustomerMigrateEvent;
use Expressly\Exception\ExceptionFormatter;
use Expressly\Exception\GenericException;
use Expressly\Lib\Customer;
use Expressly\Subscriber\CustomerMigrationSubscriber;

chdir('../../../');
require 'includes/apps/expressly/expressly.php';

try {
    if (empty($_GET['uuid'])) {
        throw new GenericException('Invalid uuid');
    }

    $uuid = $_GET['uuid'];
    $event = new CustomerMigrateEvent($merchant, $uuid);
    $dispatcher->dispatch(CustomerMigrationSubscriber::CUSTOMER_MIGRATE_DATA, $event);

    $content = $event->getContent();
    if (!$event->isSuccessful()) {
        if (!empty($content['code']) && $content['code'] == 'USER_ALREADY_MIGRATED') {
            tep_redirect('/ext/modules/expressly/exists.php');
        }

        throw new GenericException($content['message']);
    }

    $osCustomer = new Customer($app);
    if (!$osCustomer->add($uuid, $content, $language)) {
        tep_redirect('/ext/modules/expressly/exists.php');
    }
} catch (\Exception $e) {
    $logger->error(ExceptionFormatter::format($e));
}

tep_redirect('/');
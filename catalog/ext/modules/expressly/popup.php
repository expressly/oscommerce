<?php

use Expressly\Event\CustomerMigrateEvent;
use Expressly\Exception\ExceptionFormatter;
use Expressly\Exception\GenericException;
use Expressly\Lib\Customer;
use Expressly\Subscriber\CustomerMigrationSubscriber;

chdir('../../../');
require 'index.php';
require 'includes/apps/expressly/expressly.php';

try {
    if (empty($_GET['uuid'])) {
        throw new GenericException('Invalid uuid');
    }

    $uuid = $_GET['uuid'];
    $event = new CustomerMigrateEvent($merchant, $uuid);
    $dispatcher->dispatch(CustomerMigrationSubscriber::CUSTOMER_MIGRATE_POPUP, $event);

    $content = $event->getContent();
    if (!$event->isSuccessful()) {
        throw new GenericException($content['message']);
    }

    echo sprintf(
        '<script type="text/javascript" src="%s"></script>',
        DIR_WS_INCLUDES . 'apps/expressly/js/expressly.migrate.js'
    );
    echo $content;
} catch (\Exception $e) {
    $logger->error(ExceptionFormatter::format($e));

    tep_redirect('/');
}





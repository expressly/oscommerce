<?php

use Expressly\Event\CustomerMigrateEvent;
use Expressly\Exception\ExceptionFormatter;
use Expressly\Exception\GenericException;
use Expressly\Subscriber\CustomerMigrationSubscriber;

chdir('../../../');
require 'index.php';
require 'includes/apps/expressly/expressly.php';

$uuid = $_GET['uuid'];
if (empty($uuid)) {
    echo '<script>window.location.href="' . tep_href_link(FILENAME_DEFAULT) . '";</script>';

    tep_redirect(tep_href_link(FILENAME_DEFAULT));
    return;
}

try {
    $event = new CustomerMigrateEvent($merchant, $uuid);
    $dispatcher->dispatch(CustomerMigrationSubscriber::CUSTOMER_MIGRATE_POPUP, $event);

    $content = $event->getContent();
    if (!$event->isSuccessful()) {
        throw new GenericException($content['message']);
    }
    echo $content;
} catch (\Exception $e) {
    $logger->error(ExceptionFormatter::format($e));
    echo '<script>window.location.href="https://prod.expresslyapp.com/api/redirect/migration/' . $uuid . '/failed";</script>';
    return;
}





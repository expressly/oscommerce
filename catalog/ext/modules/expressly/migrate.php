<?php

use Expressly\Event\CustomerMigrateEvent;
use Expressly\Exception\ExceptionFormatter;
use Expressly\Exception\GenericException;
use Expressly\Lib\Customer;
use Expressly\Subscriber\CustomerMigrationSubscriber;

chdir('../../../');
require 'includes/apps/expressly/expressly.php';

$uuid = $_GET['uuid'];
if (empty($uuid)) {
    tep_redirect(tep_href_link(FILENAME_DEFAULT));
    return;
}

try {
    $event = new CustomerMigrateEvent($merchant, $uuid);
    $dispatcher->dispatch(CustomerMigrationSubscriber::CUSTOMER_MIGRATE_DATA, $event);

    $content = $event->getContent();
    if (!$event->isSuccessful()) {
        if (!empty($content['code']) && $content['code'] == 'USER_ALREADY_MIGRATED') {
            tep_redirect(tep_href_link('/ext/modules/expressly/exists.php'));
            return;
        }

        throw new GenericException($content['message']);
    }

    $osCustomer = new Customer($app);
    if (!$osCustomer->add($uuid, $content, $language)) {
        tep_redirect(tep_href_link('/ext/modules/expressly/exists.php'));
        return;
    }
    tep_redirect('https://prod.expresslyapp.com/api/redirect/migration/' . $uuid . '/success');
} catch (\Exception $e) {
    $logger->error(ExceptionFormatter::format($e));
    tep_redirect('https://prod.expresslyapp.com/api/redirect/migration/' . $uuid . '/failed');
    return;
}

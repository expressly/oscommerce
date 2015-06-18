<?php

use Expressly\Event\MerchantEvent;
use Expressly\Exception\ExceptionFormatter;
use Expressly\Exception\GenericException;
use Monolog\Formatter\JsonFormatter;

require 'includes/application_top.php';
require DIR_FS_CATALOG . 'includes/apps/expressly/expressly.php';

include DIR_WS_INCLUDES . 'template_top.php';

// Expressly preferences dashboard
echo $merchant->getUuid() . '<br/>';
echo $merchant->getPassword() . '<br/>';
echo $merchant->getImage() . '<br/>';
echo $merchant->getPolicy() . '<br/>';
echo $merchant->getTerms() . '<br/>';

// Handle $_POST inline, because osCommerce is shit
if (!empty($_GET['register'])) {
    try {
        $uuid = $merchant->getUuid();
        $password = $merchant->getPassword();

        if (empty($uuid) || empty($password)) {
            // seed initial data
            $host = sprintf('%s://%s', $_SERVER['REQUEST_SCHEME'], $_SERVER['HTTP_HOST']);

            $merchant
                ->setDestination('/')
                ->setPath('/ext/modules/expressly/dispatcher.php?query=')
                ->setHost($host)
                ->setImage($host . '/images/store_logo.png')
                ->setOffer((int)true)
                ->setPolicy($host . '/privacy.php')
                ->setTerms($host . '/conditions.php');

            $event = new MerchantEvent($merchant);
            $dispatcher->dispatch('merchant.register', $event);

            $content = $event->getContent();
            if (!$event->isSuccessful()) {
                throw new GenericException($content['description']);
            }

            $merchant
                ->setUuid($content['merchantUuid'])
                ->setPassword($content['secretKey']);
        }
    } catch (\Exception $e) {
        $logger->addError(ExceptionFormatter::format($e));
    }

    $provider->setMerchant($merchant);
}

include DIR_WS_INCLUDES . 'template_bottom.php';
include DIR_WS_INCLUDES . 'application_bottom.php';
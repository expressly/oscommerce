<?php

require_once 'includes/application_top.php';
require_once __DIR__ . '/vendor/autoload.php';

$client = new Expressly\Client(Expressly\Entity\MerchantType::OSCOMMERCE_2);

$app = $client->getApp();
$app['merchant.provider'] = $app->share(function () use ($app) {
    return new Expressly\Lib\MerchantProvider($app);
});

$dispatcher = $app['dispatcher'];
$provider = $app['merchant.provider'];
$merchant = $provider->getMerchant();
$logger = $app['logger'];

function formatError(Expressly\Event\ResponseEvent $event) {

}
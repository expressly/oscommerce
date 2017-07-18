<?php

require_once 'includes/application_top.php';
require_once __DIR__ . '/vendor/autoload.php';

$client = new Expressly\Client(Expressly\Entity\MerchantType::OSCOMMERCE_2);

$app = $client->getApp();
$app['merchant.provider'] = function () use ($app) {
    return new Expressly\Lib\MerchantProvider($app);
};

$dispatcher = $app['dispatcher'];
$provider = $app['merchant.provider'];
$merchant = $provider->getMerchant();
$logger = $app['logger'];

$flash = array(
    'success' => array(),
    'error' => array()
);

function formatError(Expressly\Event\ResponseEvent $event)
{
    $content = $event->getContent();
    $message = array('Something went wrong');
    if (is_array($content) && $content['description']) {
        $message = array(
            $content['description']
        );

        $addBulletpoints = function ($key, $title) use ($content, &$message) {
            if (!empty($content[$key])) {
                $message[] = '<br>';
                $message[] = $title;
                $message[] = '<ul>';

                foreach ($content[$key] as $point) {
                    $message[] = "<li>{$point}</li>";
                }

                $message[] = '</ul>';
            }
        };

        $addBulletpoints('causes', 'Possible causes:');
        $addBulletpoints('actions', 'Possible resolutions:');
    }

    return implode('', $message);
}
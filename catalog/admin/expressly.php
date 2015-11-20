<?php

use Expressly\Event\MerchantEvent;
use Expressly\Event\PasswordedEvent;
use Expressly\Exception\ExceptionFormatter;
use Expressly\Exception\GenericException;
use Expressly\Subscriber\MerchantSubscriber;
use Monolog\Formatter\JsonFormatter;

require 'includes/application_top.php';
require_once (DIR_FS_CATALOG . 'includes/apps/expressly/OSCOM_Expressly.php');

$OSCOM_Expressly = new OSCOM_Expressly();

include DIR_WS_INCLUDES . 'template_top.php';

// Expressly preferences dashboard
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $apiKey = $merchant->getApiKey();

        if (empty($apiKey)) {
            // seed initial data
            $host = sprintf('http://%s', $_SERVER['HTTP_HOST']);

            $merchant
                ->setPath('/ext/modules/expressly/dispatcher.php?query=')
                ->setHost($host);
        }

        $apiKey = !empty($_POST['expressly_apikey']) ? $_POST['expressly_apikey'] : $apiKey;
        $merchant->setApiKey($apiKey);

        $event = new PasswordedEvent($merchant);
        $dispatcher->dispatch(MerchantSubscriber::MERCHANT_REGISTER, $event);
        $provider->setMerchant($merchant);

        if (!$event->isSuccessful()) {
            throw new GenericException($OSCOM_Expressly->formatError($event));
        }

        $flash['success'][] = 'Your store is now registered.';
    } catch (\Exception $e) {
        $flash['error'][] = $e->getMessage();
        $logger->error(ExceptionFormatter::format($e));
    }
}
?>

    <link rel="stylesheet" type="text/css" href="includes/expressly.css"/>

    <div class="xly-container">
        <div class="xly-header">
            <div id="xly-header-info">
                Expressly
            </div>
        </div>

        <div class="xly-alerts">
            <?php if (!empty($flash['error'])): ?>
                <ul class="xly-alerts-error">
                    <?php
                    foreach ($flash['error'] as $error) {
                        echo "<li>{$error}</li>";
                    }
                    ?>
                </ul>
            <?php endif; ?>
            <?php if (!empty($flash['success'])): ?>
                <ul class="xly-alerts-success">
                    <?php
                    foreach ($flash['success'] as $success) {
                        echo "<li>{$success}</li>";
                    }
                    ?>
                </ul>
            <?php endif; ?>
        </div>

        <form name="xly-preferences" class="xly-form" action="expressly.php" method="POST">
            <h3 class="xly-panel-header-info">Preferences</h3>

            <div class="xly-panel xly-panel-info">
                <div>
                    <p>
                        <label>API Key</label>
                        API Key provided from our <a href="https://buyexpressly.com/#/install#api">portal</a>. If you do
                        not have an API Key, please follow the previous link for instructions on how to create one.
                    </p>

                    <div>
                        <input type="text" name="expressly_apikey" value="<?php echo $merchant->getApiKey(); ?>"/>
                    </div>
                </div>
            </div>

            <p class="xly-actions">
                <button type="submit" class="xly-button xly-button-success">Save</button>
            </p>
        </form>
    </div>

<?php

include DIR_WS_INCLUDES . 'template_bottom.php';
include DIR_WS_INCLUDES . 'application_bottom.php';
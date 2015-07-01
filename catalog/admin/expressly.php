<?php

use Expressly\Event\MerchantEvent;
use Expressly\Event\PasswordedEvent;
use Expressly\Exception\ExceptionFormatter;
use Expressly\Exception\GenericException;
use Monolog\Formatter\JsonFormatter;

require 'includes/application_top.php';
require DIR_FS_CATALOG . 'includes/apps/expressly/expressly.php';

include DIR_WS_INCLUDES . 'template_top.php';

// Expressly preferences dashboard
switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
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

                    if (!$event->isSuccessful()) {
                        throw new GenericException(formatError($event));
                    }

                    $content = $event->getContent();
                    $merchant
                        ->setUuid($content['merchantUuid'])
                        ->setPassword($content['secretKey']);

                    $provider->setMerchant($merchant);
                    $flash['success'][] = 'Your store is now registered.';
                }
            } catch (\Exception $e) {
                $flash['error'][] = $e->getMessage();
                $logger->error(ExceptionFormatter::format($e));
            }
        }
        break;
    case 'POST':
        if (empty($_POST)) {
            break;
        }

        $image = !empty($_POST['shop_image_url']) ? $_POST['shop_image_url'] : $merchant->getImage();
        $terms = !empty($_POST['shop_terms_url']) ? $_POST['shop_terms_url'] : $merchant->getTerms();
        $policy = !empty($_POST['shop_privacy_url']) ? $_POST['shop_privacy_url'] : $merchant->getPolicy();

        $merchant
            ->setImage($image)
            ->setTerms($terms)
            ->setPolicy($policy);

        try {
            $event = new PasswordedEvent($merchant);
            $dispatcher->dispatch('merchant.update', $event);

            if (!$event->isSuccessful()) {
                throw new GenericException(formatError($event));
            }

            $provider->setMerchant($merchant);
            $flash['success'][] = 'Values updated successfully.';
        } catch (\Exception $e) {
            $flash['error'][] = $e->getMessage();
            $logger->error(ExceptionFormatter::format($e));
        }
        break;
}

// Need to be set prior to usage as 5.3 is target version
$uuid = $merchant->getUuid();
$password = $merchant->getPassword();

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
                        <label>Shop Image URL</label>
                        <img src="<?php echo $merchant->getImage(); ?>"/>
                    </p>

                    <div>
                        <input type="text" name="shop_image_url" value="<?php echo $merchant->getImage(); ?>"/>
                    </div>
                </div>
            </div>

            <div class="xly-panel xly-panel-info">
                <div>
                    <p>
                        <label>Terms and Conditions URL</label>
                        URL for the Terms & Conditions for your store. <a href="<?php echo $merchant->getTerms(); ?>">Check</a>
                    </p>

                    <div>
                        <input type="text" name="shop_terms_url" value="<?php echo $merchant->getTerms(); ?>"/>
                    </div>
                </div>
            </div>

            <div class="xly-panel xly-panel-info">
                <div>
                    <p>
                        <label>Privacy Policy URL</label>
                        URL for the Privacy Policy for your store. <a
                            href="<?php echo $merchant->getPolicy(); ?>">Check</a>
                    </p>

                    <div>
                        <input type="text" name="shop_privacy_url" value="<?php echo $merchant->getPolicy(); ?>"/>
                    </div>
                </div>
            </div>

            <div class="xly-panel xly-panel-info">
                <div>
                    <p>
                        <label>Password</label>
                        Expressly password for your store
                    </p>

                    <div>
                        <input type="text" value="<?php echo $merchant->getPassword(); ?>" disabled="disabled"/>
                    </div>
                </div>
            </div>

            <p class="xly-actions">
                <?php if (!empty($uuid) && !empty($password)): ?>
                    <button type="submit" class="xly-button xly-button-success">Save</button>
                <?php else: ?>
                    <a class="xly-button xly-button-info" href="expressly.php?register=1">Register</a>
                <?php endif; ?>
            </p>
        </form>
    </div>

<?php

include DIR_WS_INCLUDES . 'template_bottom.php';
include DIR_WS_INCLUDES . 'application_bottom.php';
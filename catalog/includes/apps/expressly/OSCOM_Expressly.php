<?php

require_once __DIR__ . '/vendor/autoload.php';

use Expressly\Entity\Route;
use Expressly\Lib\Actions\CustomerActions;
use Expressly\Lib\Actions\InvoiceActions;
use Expressly\Lib\Customer;
use Expressly\Presenter\PingPresenter;
use Expressly\Presenter\RegisteredPresenter;
use Expressly\Route\BatchCustomer;
use Expressly\Route\BatchInvoice;
use Expressly\Route\CampaignMigration;
use Expressly\Route\CampaignPopup;
use Expressly\Route\Ping;
use Expressly\Route\Registered;
use Expressly\Route\UserData;

/**
 *
 */
class OSCOM_Expressly
{
    /**
     * @var \Silex\Application
     */
    public $app;

    /**
     *
     */
    public function __construct()
    {
        $client = new Expressly\Client(Expressly\Entity\MerchantType::OSCOMMERCE_2);

        $app = $client->getApp();
        $app['merchant.provider'] = $app->share(function () use ($app) {
            return new Expressly\Lib\MerchantProvider($app);
        });

        $this->app =& $app;

        $dispatcher = $this->app['dispatcher'];
        $provider   = $this->app['merchant.provider'];
        $merchant   = $provider->getMerchant();
        $logger     = $this->app['logger'];

        $flash = array(
            'success' => array(),
            'error'   => array(),
        );
    }

    /**
     *
     */
    public function runDispatcher($query)
    {
        $route = $this->app['route.resolver']->process($query);

        if ($route instanceof Route) {
            switch ($route->getName()) {
                case Ping::getName():
                    $presenter = new PingPresenter();
                    header('Content-Type: application/json');
                    echo json_encode($presenter->toArray());

                    return;
                    break;
                case Registered::getName():
                    $presenter = new RegisteredPresenter();
                    echo json_encode($presenter->toArray());

                    return;
                    break;
                case UserData::getName():
                    $data = $route->getData();
                    $ocCustomer = new Customer($this->app);
                    header('Content-Type: application/json');
                    echo json_encode($ocCustomer->get($data['email']));

                    return;
                    break;
                case CampaignPopup::getName():
                    $data = $route->getData();
                    tep_redirect("/ext/modules/expressly/popup.php?uuid={$data['uuid']}");

                    return;
                    break;
                case CampaignMigration::getName():
                    $data = $route->getData();
                    tep_redirect("/ext/modules/expressly/migrate.php?uuid={$data['uuid']}");

                    return;
                    break;
                case BatchCustomer::getName():
                    echo json_encode(InvoiceActions::getBulk($this->app));

                    return;
                    break;
                case BatchInvoice::getName():
                    echo json_encode(CustomerActions::getBulk($this->app));
                    return;
                    break;
            }
        }

        if (http_response_code() !== 401) {
            tep_redirect('/');
        }
    }

    /**
     * @param \Expressly\Event\ResponseEvent $event
     * @return string
     */
    public function formatError(Expressly\Event\ResponseEvent $event)
    {
        $content = $event->getContent();
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

        // TODO: translatable
        $addBulletpoints('causes', 'Possible causes:');
        $addBulletpoints('actions', 'Possible resolutions:');

        return implode('', $message);
    }
}

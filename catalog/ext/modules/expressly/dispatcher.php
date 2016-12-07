<?php

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

chdir('../../../');
require 'includes/apps/expressly/expressly.php';

if (empty($_GET['query'])) {
    tep_redirect('/');
}

$route = $app['route.resolver']->process($_GET['query']);

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
            header('Content-Type: application/json');
            echo json_encode($presenter->toArray());
            return;
            break;
        case UserData::getName():
            $data = $route->getData();
            $ocCustomer = new Customer($app);
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
            header('Content-Type: application/json');
            echo json_encode(CustomerActions::getBulk($app));
            return;
            break;
        case BatchInvoice::getName():
            header('Content-Type: application/json');
            echo json_encode(InvoiceActions::getBulk($app));
            return;
            break;
    }
}

if (http_response_code() !== 401) {
    tep_redirect('/');
}
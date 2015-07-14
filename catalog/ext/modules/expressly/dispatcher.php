<?php

use Expressly\Lib\Actions\CustomerActions;
use Expressly\Lib\Actions\InvoiceActions;
use Expressly\Lib\Customer;
use Expressly\Presenter\PingPresenter;

chdir('../../../');
require 'includes/application_top.php';
require sprintf('%s%s/%s', DIR_WS_LANGUAGES, $language, FILENAME_DEFAULT);
require 'includes/apps/expressly/expressly.php';

$method = $_SERVER['REQUEST_METHOD'];

if (empty($_GET['query'])) {
    tep_redirect('/');
}

$query = $_GET['query'];

switch ($method) {
    case 'GET':
        if (preg_match("/^\/?expressly\/api\/ping\/?$/", $query)) {
            $presenter = new PingPresenter();
            echo json_encode($presenter->toArray());

            return;
        }

        if (preg_match("/^\/?expressly\/api\/user\/([\w-\.]+@[\w-\.]+)\/?$/", $query, $matches)) {
            $email = array_pop($matches);
            $ocCustomer = new Customer($app);
            $ocCustomer->get($email);

            return;
        }

        if (preg_match("/^\/?expressly\/api\/([\w-]+)\/?$/", $query, $matches)) {
            $key = array_pop($matches);
            tep_redirect("/ext/modules/expressly/popup.php?uuid={$key}");

            return;
        }
        break;
    case 'POST':
        if (preg_match("/^\/?expressly\/api\/batch\/invoice\/?$/", $query)) {
            echo json_encode(InvoiceActions::getBulk($app));

            return;
        }

        if (preg_match("/^\/?expressly\/api\/batch\/customer\/?$/", $query)) {
            echo json_encode(CustomerActions::getBulk($app));

            return;
        }
        break;
}

tep_redirect('/');
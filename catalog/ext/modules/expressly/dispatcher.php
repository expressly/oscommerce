<?php

use Expressly\Lib\Customer;
use Expressly\Presenter\PingPresenter;

chdir('../../../');
require 'includes/application_top.php';
require sprintf('%s%s/%s', DIR_WS_LANGUAGES, $language, FILENAME_DEFAULT);
require 'includes/apps/expressly/expressly.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'GET' && !empty($_GET)) {
    $query = $_GET['query'];

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
}

if ($method == 'POST' && !empty($_POST)) {

}

tep_redirect('/');
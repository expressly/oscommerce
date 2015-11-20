<?php

chdir('../../../');
require('includes/application_top.php');

require_once (DIR_FS_CATALOG . 'includes/apps/expressly/OSCOM_Expressly.php');

if (empty($_GET['query'])) {
    tep_redirect('/');
} else {
    $OSCOM_Expressly = new OSCOM_Expressly();
    $OSCOM_Expressly->runDispatcher(strval($_GET['query']));
}
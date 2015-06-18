<?php

include DIR_FS_CATALOG . 'includes/apps/expressly/admin/functions/boxes.php';

$cl_box_groups[] = array(
    'heading' => 'Expressly',
    'apps' => app_expressly_get_admin_box_links()
);
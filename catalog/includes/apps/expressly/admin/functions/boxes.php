<?php

function app_expressly_get_admin_box_links() {
    $menu = array(
        array(
            'code' => 'expressly.php',
            'title' => MODULES_ADMIN_MENU_EXPRESSLY_PREFERENCES,
            'link' => tep_href_link('expressly.php')
        )
    );

    return $menu;
}
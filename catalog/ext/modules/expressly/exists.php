<?php

chdir('../../../');
require_once 'index.php';

tep_session_unregister('customer_id');
tep_session_unregister('customer_default_address_id');
tep_session_unregister('customer_first_name');
tep_session_unregister('customer_country_id');
tep_session_unregister('customer_zone_id');

if (tep_session_is_registered('sendto')) {
    tep_session_unregister('sendto');
}

if (tep_session_is_registered('billto')) {
    tep_session_unregister('billto');
}

if (tep_session_is_registered('shipping')) {
    tep_session_unregister('shipping');
}

if (tep_session_is_registered('payment')) {
    tep_session_unregister('payment');
}

if (tep_session_is_registered('comments')) {
    tep_session_unregister('comments');
}

$cart->reset();

echo sprintf(
    '<script type="text/javascript" src="%s"></script>',
    DIR_WS_INCLUDES . 'apps/expressly/js/expressly.exists.js'
);
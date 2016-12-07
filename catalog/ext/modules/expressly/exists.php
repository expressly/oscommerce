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

echo "<script> ";
echo "(function () { ";
echo "    setTimeout(function () { ";
echo "        var login = confirm('Your Email address has already been registered on this store. Please login with your credentials. Pressing OK will redirect you to the login page.'); ";
echo "        if (login) { ";
echo "            window.location.replace('" . tep_href_link('login.php') . "');";
echo "        } ";
echo "    }, 500); ";
echo " })();";
echo "</script> ";

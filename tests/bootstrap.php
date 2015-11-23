<?php

// Command that starts the built-in web server
$command = sprintf(
    'php -S %s:%d -t %s >/dev/null 2>&1 & echo $!', WEB_SERVER_HOST, WEB_SERVER_PORT, WEB_SERVER_WEBROOT
);

// Execute the command and store the process ID
$output = [];
exec($command, $output);
$pid = intval($output[0]);

echo sprintf(
        '%s :: Web server started on %s:%d with PID %d',
        date('r'),
        WEB_SERVER_HOST,
        WEB_SERVER_PORT,
        $pid
    ) . PHP_EOL;

// Kill the web server when the process ends
register_shutdown_function(function() use ($pid) {
    echo sprintf('%s :: Killing process with ID %d', date('r'), $pid) . PHP_EOL;
    exec('kill ' . $pid);
});

require_once('catalog/includes/apps/expressly/OSCOM_Expressly.php');
require_once('vendor/autoload.php');

$db = new PDO('mysql:host='.DB_HOST.';dbname='.DB_DBNAME.';charset=utf8', DB_USERNAME, DB_PASSWORD);
$db->exec(sprintf(
    'INSERT INTO %s (`configuration_title`, `configuration_value`, `configuration_key`, `configuration_description`, `configuration_group_id`, `sort_order`) VALUES ("%s", "%s", "%s", "", 0, 6);',
    'configuration',
    'Expressly Preferences',
    OSCOM_APP_EXPRESSLY_APIKEY,
    'OSCOM_APP_EXPRESSLY_APIKEY'
));

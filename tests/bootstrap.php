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



//$url = "http://github.com/osCommerce/oscommerce2/archive/v2.3.4.zip";

//$zipFile = realpath(__DIR__ . '/../tmp') . '/oscommerce2.zip'; // Local Zip File Path


// curl -o oscommerce2.zip -L http://github.com/osCommerce/oscommerce2/archive/v2.3.4.zip

/*file_put_contents($zipFile,
    file_get_contents($url)
);*/
/*
$zip = new ZipArchive;
$extractPath = realpath(__DIR__ . '/../tmp');
if($zip->open($zipFile) != "true"){
    echo "Error :- Unable to open the Zip File";
} else {
    for($i = 0; $i < $zip->numFiles; $i++) {
        $filename = $zip->getNameIndex($i);
        $fileinfo = pathinfo($filename);

        if (0 === strpos($fileinfo['dirname'], 'oscommerce2-2.3.4/catalog')) {
            if (!file_exists(dirname($extractPath . '/public' . str_replace('oscommerce2-2.3.4/catalog', '', $fileinfo['dirname']) . '/' . $fileinfo['basename']))) {
                var_dump(dirname($extractPath . '/public' . str_replace('oscommerce2-2.3.4/catalog', '', $fileinfo['dirname']) . '/' . $fileinfo['basename']));
                //mkdir(dirname($extractPath . '/public' . str_replace('oscommerce2-2.3.4/catalog', '', $fileinfo['dirname']) . '/' . $fileinfo['basename']));
            }

            if (!is_dir($extractPath . '/public' . str_replace('oscommerce2-2.3.4/catalog', '', $fileinfo['dirname']) . '/' . $fileinfo['basename']))
                copy('zip://'.$zipFile.'#'.$filename, $extractPath . '/public' . str_replace('oscommerce2-2.3.4/catalog', '', $fileinfo['dirname']) . '/' . $fileinfo['basename']);
        }

    }
    $zip->close();
}*/




///unlink($extractPath.'/public');
//mkdir($extractPath.'/public');
/* Extract Zip File */
//var_dump($zip->extractTo($extractPath.'/public', 'oscommerce2-2.3.4/catalog/'));
//$zip->close();


/*

// Do something
echo "=====\n";
echo realpath(__DIR__.'/../../webroot/') . "\n";
echo "=====\n";
basename(realpath(__DIR__.'/../../webroot/'));
set_include_path(get_include_path() . PATH_SEPARATOR . realpath(__DIR__.'/../../webroot/'));
var_dump(get_include_path());

require_once 'includes/application_top.php';*/
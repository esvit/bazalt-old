<?php

// respond to preflights
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    // return only the headers and not the content
    // only allow CORS if we're doing a GET - i.e. no saving for now.
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']) && $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] == 'GET') {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: X-Requested-With');
    }
    exit;
}

define('SITE_DIR', __DIR__);
define('ERROR_LOG_FILE', SITE_DIR . '/fixme.log');
define('GENERATOR_EXPOSE', false);

define('TEMP_DIR', SITE_DIR . '/tmp');

// Include BAZALT framework
require_once 'Framework/Core/include.inc';

if (!is_file('config.php') || !filesize('config.php')) {
    header('Location: /install.php');
    exit;
}
require_once 'config.php';

Framework\System\Session\Session::setTimeout(30 * 24 * 60 * 60);
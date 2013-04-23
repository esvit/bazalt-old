<?php

use Framework\CMS\Http\Request;

define('ROUTING_NO_SCRIPT_NAME', true);
define('DEBUG', false);

include 'bootstrap.php';

Framework\CMS\Bootstrap::start(
    new App\Site\Application(array(
        'request' => new Request()
    ))
);
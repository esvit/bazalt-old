<?php

define('ROUTING_NO_SCRIPT_NAME', true);

use Framework\CMS\Http\Request;

include 'bootstrap.php';

Framework\CMS\Bootstrap::start(
    new App\Rest\Application([
        'request' => new Request()
    ])
);
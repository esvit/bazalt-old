<?php

use Framework\CMS\Http\Request;

define('DEBUG', false);

include 'bootstrap.php';

Framework\CMS\Bootstrap::start(
    new App\Admin\Application([
        'request' => new Request()
    ])
);
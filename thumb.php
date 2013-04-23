<?php

use Framework\CMS\Http\Request;

include 'bootstrap.php';

Framework\CMS\Bootstrap::start(
    new App\Thumb\Application([
        'request' => new Request()
    ])
);
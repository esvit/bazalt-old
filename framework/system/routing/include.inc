<?php

// add trailed slash in url
// example:  http://../test => http://.../test/
if (!defined('SEO_LAST_SLASH_IN_URL')) {
    define('SEO_LAST_SLASH_IN_URL', true);
}

Core_Autoload::registerNamespace('Routing', dirname(__FILE__));

require_once 'Mapper.php';
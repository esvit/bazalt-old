<?php

namespace Tests;

define('DEBUG', true);
define('SITE_DIR', dirname(__FILE__) . '/..');

date_default_timezone_set('Europe/Kiev');

require_once SITE_DIR . '/Framework/Core/include.inc';

\Framework\Core\Autoload::registerNamespace('Tests', dirname(__FILE__));
<?php

define('SITE_DIR', dirname(__FILE__)); // no trailing slash
define('ERROR_LOG_FILE', SITE_DIR . '/fixme.log');

// Include BAZALT framework
require_once SITE_DIR . '/framework/Core/include.inc';

using('Framework.CMS');
CMS_Bootstrap::start();
<?php
/**
 * Initialize framework core
 *
 * @category  Core
 * @package   BAZALT
 * @copyright 2010 Equalteam
 * @license   GPLv3
 * @version   SVN: $Revision: 178 $
 * @link      http://bazalt-cms.com/
 */

namespace Framework\Core;

/**
 * @since PHP 5.3.0
 */
if (!defined('E_DEPRECATED')) {
    define('E_DEPRECATED',              8192);
}
if (!defined('E_USER_DEPRECATED')) {
    define('E_USER_DEPRECATED',         16384);
}

if (version_compare(PHP_VERSION, '5.4.0', '<')) {
    die('Update you PHP first. Needed 5.4.0 min. You are using ' . PHP_VERSION . ' now;');
}

@date_default_timezone_set(@date_default_timezone_get());

/**
 * Check run mode
 */
define('CLI_MODE',                      (php_sapi_name() == 'cli'));

/**
 * for beatifull life (antigovnokod :)
 */
ini_set('short_open_tag',               false);
ini_set('asp_tags',                     false);

ini_set('arg_separator.output',         '&'); // special for PHP 5.3
ini_set('default_charset',              'UTF-8');  // default encoding

// Some features need this option
ini_set('allow_call_time_pass_reference', 1);

/**
 * Path to core folder
 */
define('CORE_DIR',                      __DIR__);

if (!defined('SITE_DIR')) {
    die('Constant SITE_DIR not defined!');
}

if (!defined('FRAMEWORK_DIR')) {
    /**
     * Framework
     */
    define('FRAMEWORK_DIR',             realpath(CORE_DIR . '/..'));
}

/**
 * Розділювач шляху
 */
define('PATH_SEP',                      DIRECTORY_SEPARATOR);

// OS
if (!defined('OS')) {
    define('OS_WIN',                    'WIN');
    define('OS_NIX',                    'NIX');

    define('OS',                        (strToUpper(substr(PHP_OS, 0, 3)) === 'WIN') ? OS_WIN : OS_NIX);
}

define('BAZALT_NS',                     'http://bazalt-cms.com/xml/config/');

// debug
if (!defined('DEBUG')) {
    define('DEBUG',                     false);
}

define('DEVELOPMENT_STAGE',             'development');
define('PRODUCTION_STAGE',              'production');
define('TESTING_STAGE',                 'testing');

if (defined('APPLICATION_ENV') && in_array(APPLICATION_ENV, array(DEVELOPMENT_STAGE, PRODUCTION_STAGE, TESTING_STAGE))) {
    define('STAGE', APPLICATION_ENV);
} else {
    if (getenv('APPLICATION_ENV') && !isset($_GET['debug'])) {
        switch(getenv('APPLICATION_ENV')) {
        case PRODUCTION_STAGE:
        case TESTING_STAGE:
            define('STAGE', getenv('APPLICATION_ENV'));
            break;
        default:
            define('STAGE', DEVELOPMENT_STAGE);
        }
    } else {
        // deprecated
        define('STAGE',
            (isset($_SERVER['PRODUCTION']) && !DEBUG) ? PRODUCTION_STAGE :
            (isset($_SERVER['TESTING']) ? TESTING_STAGE : DEVELOPMENT_STAGE)
        );
    }
}

// error logging
if (!defined('ERROR_LOG_FILE')) {
    define('ERROR_LOG_FILE',            SITE_DIR . '/fixme.log');
}
ini_set('ignore_repeated_errors',       false);
ini_set('ignore_repeated_source',       false);
ini_set('track_errors',                 true); // ??
ini_set('html_errors',                  false);

if (STAGE == PRODUCTION_STAGE) {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_NOTICE);

    // Log errors
    ini_set('log_errors',               true);
    ini_set('log_errors_max_len',       '100M');
    ini_set('error_log',                ERROR_LOG_FILE);
    ini_set('display_errors',           false);
    ini_set('display_startup_errors',   false);
    
    // disable xdebug for catching fatal errors
    if (extension_loaded('xdebug')) {
        xdebug_disable();
    }
} else {
    if(!defined('E_RECOVERABLE_ERROR')) define('E_RECOVERABLE_ERROR', 4096);
    error_reporting(E_ALL | E_STRICT);

    ini_set('log_errors',               false);

    ini_set('display_errors',           true);
    ini_set('display_startup_errors',   true);
    ini_set('report_memleaks',          true);
}

define('EXT_MBSTRING',                  extension_loaded('mbstring'));

// Check mbstring extension
if (!EXT_MBSTRING) {
    die('System require mbstring extension');
}

define('ALLOW_UPLOADS',                 ini_get('file_uploads'));
if (ALLOW_UPLOADS) {
    $uploadMaxSize = ini_get('upload_max_filesize');
    $mul = substr($uploadMaxSize, -1);
    $mul = ($mul == 'M' ? 1048576 : ($mul == 'K' ? 1024 : ($mul == 'G' ? 1073741824 : 1)));
    define('UPLOAD_MAX_SIZE',           $mul * (int)$uploadMaxSize);
}

// set default mbstring encoding, system work only with UTF-8
mb_internal_encoding('UTF-8');

require_once CORE_DIR . '/Autoload.php';
require_once CORE_DIR . '/Logger.php';
Autoload::register();
Autoload::registerNamespace('Framework', FRAMEWORK_DIR);

Logger::getInstance()->startLog();

require_once CORE_DIR . '/helpers/include.inc';

require_once CORE_DIR . '/IDNConvertor.php';
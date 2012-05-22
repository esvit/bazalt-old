<?php

define('CORE_FROM_PHAR', true);
define('FRAMEWORK_DIR', dirname(str_replace('phar://', '', __FILE__)));
if (!defined('SITE_DIR')) {
    define('SITE_DIR', realpath(FRAMEWORK_DIR . '/../..'));
}

Phar::mapPhar();
//print_r($argv);

require 'phar://' . __FILE__ . '/include.inc';

if (php_sapi_name() == 'cli' && isset ($argv[1]) && $argv[1] == 'test') {
    echo "Running Unit Tests\n";
    require_once 'PHPUnit/Autoload.php';
    require_once CORE_DIR . '/tests/AllTests.php';

    $listener = new PHPUnit_TextUI_ResultPrinter(null, true);

    //set_include_path(get_include_path() . PATH_SEPARATOR . 'MyLib.phar');
    //require_once 'phar://MyLib/tests/unit-tests/UnitTests.php';
    $suite = AllTests::suite();
    $result = new PHPUnit_Framework_TestResult;
    $result->addListener($listener);
    $suite->run($result);
    
    $listener->printResult($result);
    die((int)$result->wasSuccessful());
}
__HALT_COMPILER();
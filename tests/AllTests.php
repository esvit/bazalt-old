<?php
// @codeCoverageIgnoreStart

namespace tests;
use \Framework\Core as Core;

define('DEBUG', true);
define('SITE_DIR', dirname(__FILE__) . '/..');

define('APPLICATION_ENV', 'testing');

date_default_timezone_set('Europe/Kiev');

require_once SITE_DIR . '/Framework/Core/include.inc';

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'AllTests::suite');
}

error_reporting(E_ALL & ~E_DEPRECATED | E_STRICT);

Core\Autoload::registerNamespace('tests', dirname(__FILE__));

/*
using('Framework.CMS');
\CMS_Bootstrap::start('Site');
*/
class AllTests
{
    public static $ignoreList = array('vendors');

    public static function suite()
    {
        //PHP_CodeCoverage_Filter::getInstance()->addDirectoryToBlacklist(dirname(__FILE__));

        return self::init();
    }

    public static function init()
    {
        $suite = new \PHPUnit_Framework_TestSuite('All tests');

        $test = null;
        foreach ($_SERVER['argv'] as $args) {
            if (stristr($args,'tests') !== false ) {
                $tmp = explode('=', $args);
                $test = isset($tmp[1]) ? $tmp[1] : null;
            }
        }

        if ($test != null && !is_dir($test) && file_exists($test)) {
            require_once $test;
        } else {
            if ($test != null && !is_dir($test)) {
                exit('Test folder '.$test.' not found');
            }
            $path = ($test == null) ? SITE_DIR : $test;
            echo 'Scan folder for tests: ' . realpath($path) . "\n";
            AllTests::scanFolder(realpath($path));
        }
        
        $classes = get_declared_classes();
        foreach ($classes as $class) {
            if (typeOf($class)->isSubclassOf('\tests\BaseCase')) {
                $suite->addTestSuite($class);
            }
        }        

        return $suite;
    }

    private static function scanFolder($folder)
    {
        $testList = array();
        if (in_array(basename($folder), self::$ignoreList) || realpath($folder) == dirname(__FILE__)) {
            return $testList;
        }

        if (!$handle = opendir($folder)) {
            throw new Exception('Cannot open dir '.$folder);
        }
        while (false !== ($file = readdir($handle))) {
            if ($file != '.' && $file != '..') {
                if (substr($file, -5) == '.test') {
                    echo "Found test case: " . $file . "\n";

                    require_once $folder . '/' . $file;
                    $testList []= $folder . '_' . substr($file, 0, -4);
                }
                if (is_dir($folder . '/' . $file)) {
                    $testList = array_merge($testList, self::scanFolder($folder . '/' . $file));
                }
            }
        }
        closedir($handle);
        return $testList;
    }
}
// @codeCoverageIgnoreEnd
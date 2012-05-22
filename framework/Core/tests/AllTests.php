<?php

#date_default_timezone_set('Europe/Kiev');

require_once dirname(__FILE__) . '/BaseTestCase.php';

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'AllTests::suite');
}

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
        // self::addUsings();
        $suite = new PHPUnit_Framework_TestSuite('All tests');

        $test = null;
        if( !empty( $_SERVER['argv'][2] ) && stristr($_SERVER['argv'][2],'tests') !== false ) {
            $tmp = explode('=',$_SERVER['argv'][2]);
            $test = $tmp[1];
        }        
        //exit($test);
        if( $test != null ) {
            if( !file_exists($test) ) {
                exit('Test '.$test.' not found');
            }
            require_once $test;
        } else {
            echo 'Scan folder for tests: ' . realpath(dirname(__FILE__) . '/../') . "\n";
            AllTests::scanFolder(CORE_DIR);//realpath(dirname(__FILE__) . '/../'));
        }
        
        $classes = get_declared_classes();
        foreach ($classes as $class) {
            if (typeOf($class)->isSubclassOf('BaseTestCase')) {
                $suite->addTestSuite($class);
            }
        }        

        return $suite;
    }

    public static function addUsings()
    {
        using('Framework.System.Orm');
        using('Framework.System.Session');
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
                if (substr($file, -4) == 'test') {
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
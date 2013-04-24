<?php
/**
 * Autoload
 *
 * @category  Core
 * @package   Core
 * @copyright 2010 Equalteam
 * @license   GPLv3
 * @version   SVN: $Revision: 133 $
 */

namespace Framework\Core;

/**
 * Autoload
 *
 * @category  Core
 * @package   Core
 * @copyright 2010 Equalteam
 * @license   GPLv3
 * @version   $Revision: 133 $
 */
class Autoload
{
    /**
     * @var array Усі зареєстровані області імен
     */
    protected static $namespaces = array();

    /**
     * Registers Autoload as an SPL autoloader.
     *
     * @return void
     */
    public static function register()
    {
        ini_set('unserialize_callback_func', 'spl_autoload_call');
        spl_autoload_register(array(__CLASS__, 'autoload'));
    }

    /**
     * Registers namespace for autoload
     *
     * @param string $prefix Prefix of classes
     * @param string $dir    Directory
     *
     * @throws Exception
     * @return void
     */
    public static function registerNamespace($prefix, $dir)
    {
        $dir = rtrim($dir, '/');
        if (isset(self::$namespaces[$prefix]) && realpath(self::$namespaces[$prefix]) != realpath($dir)) {
            throw new Exception('Namespace prefix "' . $prefix . 
                                '" already registred to "' . self::$namespaces[$prefix] . 
                                '". You try set it to "' . $dir . '"');
        }
        self::$namespaces[$prefix] = $dir;
    }

    /**
     * Return filename for class name
     *
     * @param string $class Class name
     * @return string Path to file
     */
    public static function getFilename($class)
    {
        $symb = (strpos($class, '\\') !== false) ? '\\' : '_';

        $prefix = substr($class, 0, strpos($class, $symb));

        $dir = dirname(__FILE__);
        if (array_key_exists($prefix, self::$namespaces)) {
            $dir = self::$namespaces[$prefix];

            $class = substr($class, strpos($class, $symb) + 1);
        } else {
            $dir = SITE_DIR;
        }
        return $dir . PATH_SEP . str_replace($symb, '/', $class) . '.php';
    }

    /**
     * Handles autoloading of classes.
     *
     * @param string $class A class name.
     *
     * @return boolean Returns true if the class has been loaded
     */
    public static function autoload($class)
    {
        $fileName = self::getFilename($class);

        if (is_readable($fileName)) {
            include_once $fileName;
        } else {
            Logger::getInstance()->info('Try loading class ' . $class . '. File ' . $fileName . ' found ', __CLASS__);
        }
        return true;
    }
}

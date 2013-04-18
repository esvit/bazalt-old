<?php
/**
 * Library
 *
 * @category   Core
 * @package    Core
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    SVN: $Revision: 154 $
 * @link       http://bazalt-cms.com/
 */

namespace Framework\Core;

/**
 * Library
 *
 * @category   Core
 * @package    Core
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    Release: $Revision: 154 $
 */
class Library
{
    const CONST_DIR_POSTFIX = '_DIR';

    const SEPARATOR = '.';

    const INCLUDE_FILE = 'include.inc';

    protected $path;

    protected $includeFile = self::INCLUDE_FILE;

    protected $namespaceString = null;

    protected $names = array();

    protected static $libraries = array();

    public function __construct($namespace, $includeFile = self::INCLUDE_FILE)
    {
        if (!self::isValid($namespace)) {
            throw new Exception\InvalidNamespace('Invalid namespace "' . $namespace . '"');
        }

        $this->names = explode(self::SEPARATOR, $namespace);
        $this->namespaceString = $namespace;

        $this->path = Helper\String::replaceConstants($this->getPath());
        $this->includeFile = $includeFile;
    }

    public static function create($namespace)
    {
        if (array_key_exists($namespace, self::$libraries)) {
            return self::$libraries[$namespace];
        }

        if (!self::isValid($namespace)) {
            throw new Exception\InvalidNamespace('Invalid namespace "' . $namespace . '"');
        }

        return new Library($namespace);
    }

    public function getFilename()
    {
        $filename = $this->includeFile;

        $path = $this->path . PATH_SEP;

        $fileName = realpath($path . $filename);

        if (!$fileName) {
            Autoload::registerNamespace($this->getAutoloadPrefix(), $path);
            return null;
        }
        return $fileName;
    }

    public function getAutoloadPrefix()
    {
        return end($this->names);
    }

    public function getPath()
    {
        $path = $this->findPathByNamespace();

        if (!empty($path)) {
            return $path;
        }

        $tmp = $this->names;
        unset($tmp[0]);
        $subDir = implode(PATH_SEP, $tmp);

        # Generate constant name
        $constName = $this->names[0];
        $constName = strToUpper($constName) . self::CONST_DIR_POSTFIX;

        # Check constant
        if (defined($constName)) {
            $path = constant($constName);
            $dir = $path . PATH_SEP . $subDir;
            if (!is_dir($dir)) { // remove from here
                $chunks = explode('/',$subDir);
                for ($i = 0; $i < count($chunks) - 1; $i++) {
                    $chunks[$i] = strToLower($chunks[$i]);
                }
                $subDir = implode('/', $chunks);
                $dir = $path . PATH_SEP . $subDir;
                if (!is_dir($dir)) {
                    $dir = $path . PATH_SEP . strToLower($subDir);
                }
            } // remove to here
            return $dir; 
        }
        return null;
    }

    public static function isValid($string)
    {
        return (preg_replace('/([A-Za-z0-9_]+)\.?/i', '', $string) == '');
    }

    public function findPathByNamespace()
    {
        $namespace = $this->namespaceString;
        if (self::isLibraryExists($namespace)) {
            $path = self::$libraries[$namespace]->getPath();
            return;
        }

        foreach (self::$libraries as $k => $library) {
            if (strpos($namespace, $k . self::SEPARATOR) === 0) {
                $subdir = substr($namespace, strlen($k) + 1);
                $subdir = str_replace(self::SEPARATOR, PATH_SEP, $subdir);
                $subdir = strtolower($subdir);
                $path = $library->Path . PATH_SEP . $subdir;
                return;
            }
        }
    }

    public static function isLibraryExists($namespace)
    {
        return array_key_exists($namespace, self::$libraries);
    }
}

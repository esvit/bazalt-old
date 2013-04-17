<?php
/**
 * Object class file
 *
 * @category  Core
 * @package   Core
 * @copyright 2010 Equalteam
 * @license   GPLv3
 * @version   SVN: $Revision: 151 $
 * @link      http://bazalt-cms.com/
 *
 * PHP Version 5
 */

namespace Framework\Core;

/**
 * Основний клас, що описує об'єкт системи BAZALT
 *
 * Його наслідують всі інші об'єкти системи. Основне призначення забезпечення 
 * встановлення та зчитування властивостей об'єктів та інших параметрів.
 * 
 * В кожному об'єкті передбачено можливість використання функцій встановлення 
 * і отримнання значень змінних (властивостей).
 *
 * @category  Core
 * @package   Core
 * @copyright 2010 Equalteam
 * @license   GPLv3
 * @version   Release: $Revision: 151 $
 * @property-read string  $ObjectName  Unique name of object
 * @property-read boolean $IsSingleton Singleton flag
 * @see       Object, IEventable
 * @todo      add some documentation
 */
abstract class Object
{
    /**
     * Префікс функції, яка викликається перед записом змінної класа. Наприклад,
     * клас MyClass містить змінну MyData. Назва функції що встановлює її 
     * значення повинна складатись з WRITE_PREFIX_FUNCTION та *ім'я змінної*, 
     * тобто *setMyData*
     * @example event.class.inc
     */
    const WRITE_PREFIX_FUNCTION = 'set';

    /**
     * Префікс функції, яка викликається перед зчитуванням змінної класа. 
     * Наприклад, клас MyClass містить змінну MyData. Назва функції що зчитує її
     * значення повинна складатись з READ_PREFIX_FUNCTION та *ім'я змінної*, 
     * тобто *getMyData*. Такій підхід дозволяє виконувати додаткову обробку, 
     * операції за подією отримання значень змінної.
     */
    const READ_PREFIX_FUNCTION = 'get';

    /**
     * Singleton flag
     *
     * @var boolean
     */
    protected $isSingleton = false;

    /**
     * Масив усіх класів що зареєструвались в системі
     *
     * @var array
     */
    private static $_objects = array();

    /**
     * Array of extensions
     *
     * @var array
     */
    protected static $extensions = array();

    /**
     * Повертає масив усіх класів(сінглтонів), що зареєструвались в системі
     *
     * @return array
     */
    public static function getAllObjects()
    {
        return self::$_objects;
    }

    /**
     * Реалізує статичність класу в системі.
     * Надає можливість уникнути глобальних змінних PHP.
     *
     * @param  string optional class name
     *
     * @return object
     */
    public static function &Singleton($className = null)
    {
        if($className == null) {
            $className = (func_num_args() > 0) ? func_get_arg(0) : 
                                                (function_exists('get_called_class') ? get_called_class() : getCalledClass());
        }
        $instance = call_user_func(array($className, 'getInstance'), $className);

        return $instance;
    }

    /**
     * Реалізує статичність класу в системі.
     * Надає можливість уникнути глобальних змінних PHP.
     *
     * @param  string $className Class name
     *
     * @return object
     */
    public static function &getInstance()
    {
        $className = (func_num_args() > 0) ? func_get_arg(0) : getCalledClass();

        if (!$className || $className == 'self') {
            throw new Exception\Singleton();
        }

        $lowerName = strToLower($className);
        if (!isset(self::$_objects[$lowerName]) || !is_object(self::$_objects[$lowerName])) {
            Logger::getInstance()->info('Create singleton for ' . $className);

            $class = new $className();

            if (!$class instanceOf Interfaces\Singleton) {
                throw new Exception\InvalidInterface('Interfaces\Singleton', $className);
            }
            if ($class instanceOf Object) {
                $class->isSingleton = true;
            }
            self::$_objects[$lowerName] = $class;
        }
        return self::$_objects[$lowerName];
    }

    /**
     * ???
     *
     * @param string Class name
     */
    public static function extend($className, $classExtensionName)
    {
        if (!DataType_String::isValid($classExtensionName)) {
            throw new InvalidArgumentException('Invalid argument');
        }
        if (!$className) {
            throw new Exception('Cannot detect extend class');
        }

        if (!array_key_exists($className, self::$extensions)) {
            self::$extensions[$className] = array();
        }
        if (!in_array($classExtensionName, self::$extensions[$className])) {
            self::$extensions[$className][] = $classExtensionName;
        }
    }

    /**
     * Protected constructor for Singleton
     */
    protected function __construct()
    {
    }

    public function __clone()
    {
        if ($this instanceof Interfaces\Singleton) {
            throw new Exception\Singleton('Object allow only single instance exists');
        }
    }

    /**
     * Get class name of object
     *
     * @return Type
     */
    public function getType()
    {
        return new Type($this);
    }

    /**
     * Get logger for current object
     *
     * @return Logger
     */
    public function getLogger()
    {
        return new Logger(get_class($this));
    }

    /**
     * Get hash of object
     *
     * @return string
     */
    public function getHash()
    {
        return spl_object_hash($this);
    }

    /**
     * Convert an object into an associative array
     *
     * This function converts an object into an associative array by iterating
     * over its public properties. Because this function uses the foreach
     * construct, Iterators are respected. It also works on arrays of objects.
     *
     * @return array
     */
    public function toArray()
    {
        static $keys = array();
        $var = $this;
        $result = array();
        $references = array();

        // loop over elements/properties
        foreach ($this as $key => $value) {
            // recursively convert objects
            if (is_object($value) || is_array($value)) {
                // but prevent cycles
                if (!in_array($value, $references)) {
                    if (is_object($value)) {
                        $objKey = spl_object_hash($value);
                    }
                    if (!isset($objKey[$keys])) {
                        if ($value instanceof Object) {
                            $result[$key] = $value->toArray();
                        } else {
                            $result[$key] = $this->toArray($value);
                        }
                        if (is_object($value)) {
                            $keys[] = $objKey;
                        }
                    }
                    $references[] = $value;
                }
            } else {
                // simple values are untouched
                $result[$key] = $value;
            }
        }
        return $result;
    }

    public function __get($property)
    {
        $methodName = self::READ_PREFIX_FUNCTION . ucfirst($property);

        if (method_exists($this, $methodName)) {
            return $this->$methodName();
        }

        $propName = lcfirst($property);
        if ($property != $propName && property_exists($this, $propName)) {
            return $this->$propName;
        }

        $eventName = Event::EVENT_PREFIX . $property;
        if (property_exists($this, $eventName) && ($this instanceof Interfaces\Eventable)) {
            if ($this->$eventName == Event::EMPTY_EVENT) {
                $this->$eventName = Event::initEvent($this, $property);
            }
            return $this->$eventName;
        }
        throw new Exception\Property($property, Exception\Property::UNDEFINED);
    }

    public function __set($property, $value)
    {
        $methodName = self::WRITE_PREFIX_FUNCTION . ucfirst($property);

        if (method_exists($this, $methodName)) {
            $this->$methodName($value);
            return;
        }

        $propName = lcfirst($property);
        if ($property != $propName && property_exists($this, $propName)) {
            throw new Exception\Property($property, Exception\Property::READONLY);
        }

        $eventName = Event::EVENT_PREFIX . $property;
        if (property_exists($this, $eventName) && ($this instanceof Interfaces\Eventable)) {

            if ($this->$eventName == Event::EMPTY_EVENT) {
                $this->$eventName = Event::initEvent($this, $property);
            }

            $event = $this->$eventName;
            $event->removeAll();
            $event->add($value);
        } else {
            throw new Exception\Property($property, Exception\Property::UNDEFINED);
        }
    }

    public function __call($func, $args = array())
    {
        $eventName = Event::EVENT_PREFIX . $func;

        if (property_exists($this, $eventName) && $this instanceof Interfaces\Eventable) {
            $event = Event::get($this, $func);

            if (is_object($event)) {
                $event->raise($args);
                return $event;
            }
        }

        $className = get_class($this);
        if (array_key_exists($className, self::$extensions)) {
            $exts = self::$extensions[$className];
            foreach ($exts as $ext) {
                $extObject = Type::getObjectInstance($ext);
                if ($extObject->getType()->hasMethod($func)) {
                    $args = array_merge(array($this), $args);
                    return call_user_func_array(array($extObject, $func), $args);
                }
            }
        }
        throw new \Exception('Cannot find function ' . $func);
    }
}
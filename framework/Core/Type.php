<?php
/**
 * File with class Type
 *
 * @category   Core
 * @package    BAZALT
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    SVN: $Revision: 155 $
 * @link       http://bazalt-cms.com/
 */

/**
 * Клас, що реалізовує роботу з типами даних
 *
 * @category  Core
 * @package   BAZALT
 * @copyright 2010 Equalteam
 * @license   GPLv3
 * @version   Release: $Revision: 155 $
 */
class Type extends Object
{
    /**
     * Данні, які були передані класу у конструкторі
     *
     * @var object|string
     */
    private $data;

    /**
     * Назва класу
     *
     * @var string
     */
    protected $className;

    /**
     * Усі класи рефлексії, що викликались
     */
    protected static $arrTypes = array();

    /**
     * Повертає клас рефлексії для класу
     *
     * @param  $type           Назва класу
     * @return ReflectionClass Клас рефлексії
     */
    protected static function getRefType($type)
    {
        if (!array_key_exists($type, self::$arrTypes)) {
            self::$arrTypes[$type] = new ReflectionClass($type);
        }
        return self::$arrTypes[$type];
    }

    /**
     * Конструктор
     *
     * @param object|string $mixed Об'єкт або назва класу
     * @throws InvalidArgumentException Якщо параметер не вірного типу, або назва класу, який не існує
     */
    public function __construct($mixed)
    {
        $this->data = $mixed;
        if (is_string($mixed)) {
            if (!class_exists($mixed)) {
                throw new InvalidArgumentException('Class ' . $mixed . ' not found');
            }
            $this->className = $mixed;
        } else if (is_object($mixed)) {
            $this->className = get_class($mixed);
        } else {
            throw new InvalidArgumentException('Invalid argument for class Type. Must be object or string, given ' . gettype($mixed));
        }
        parent::__construct();
    }

    /**
     * Повертає усі визначенні імена класів
     *
     * @return array Масив назв класів
     */
    public static function getDeclaredClasses()
    {
        return get_declared_classes();
    }

    /**
     * Дізнатись чи наслідує клас інтерфейс
     *
     * @param string $interfaceName Назва інтерфейсу
     * @return bool true - якщо клас наслідує даних інтерфейс, або false - якщо ні
     */
    public function hasInterface($interfaceName)
    {
        return self::getRefType($this->className)->implementsInterface($interfaceName);
    }

    /**
     * Дізнатись чи клас абстрактний
     *
     * @return bool true - якщо клас абстрактний, або false - якщо ні
     */
    public function isAbstract()
    {
        return self::getRefType($this->className)->isAbstract();
    }

    /**
     * Дізнатись чи клас наслідується від заданого класу
     *
     * @param string $parentClassName Назва класу 
     * @return bool true - якщо клас наслідується від $parentClassName, або false - якщо ні
     */
    public function isSubclassOf($parentClassName)
    {
        $parent = new ReflectionClass($parentClassName);
        return self::getRefType($this->className)->isSubclassOf($parent);
    }

    /**
     * Перевіряє чи існує заданий клас
     *
     * @param string $className Назва класу 
     * @return bool true - якщо клас існує, або false - якщо ні
     */
    public static function isClassExists($className)
    {
        return class_exists($className);
    }

    /**
     * Повертає список властивостей класу
     */
    public function getProperties($filter = ReflectionMethod::IS_PUBLIC)
    {
        return self::getRefType($this->className)->getProperties($filter);
    }

    /**
     * Створює екземпляр класу
     */
    public function createInstance($args = array())
    {
        $constr = self::getRefType($this->className)->getConstructor();
        if (!$constr->isPublic() && $this->hasInterface('ISingleton')) {
            return Object::Singleton($this->className);
        }
        if (!is_array($args)) {
            $args = array($args);
        }
        return self::getRefType($this->className)->newInstanceArgs($args);
    }

    /**
     * Повертає файл де знаходиться клас
     */
    public function getFileName()
    {
        return self::getRefType($this->className)->getFileName();
    }

    /**
     * Повертає події класу
     */
    public function getEvents()
    {
        if (!$this->hasInterface('IEventable')) {
            # Maybe warning
            return null;
        }
        $vars = $this->getProperties();

        $events = array();
        foreach ($vars as $property) {
            $prefixLen = strlen(Event::EVENT_PREFIX);

            if (substr($property->name, 0, $prefixLen) == Event::EVENT_PREFIX &&
                !$property->isStatic()) {
                $eventName = substr($property->name, $prefixLen);
                $events[] = $eventName;
            }
        }
        return $events;
    }

    /**
     * 
     */
    public function bind($name, $callback)
    {
        if (!$this->hasInterface('IEventable')) {
            throw new Exception('Object must implements IEventable interface');
        }
        if (is_object($this->data)) {
            $this->data->{Event::EVENT_PREFIX . $name}->add($callback);
        } else {
            Event::register($this->className, $name, $callback);
        }
    }

    public function unbind($name = null)
    {
        if (!$this->hasInterface('IEventable')) {
            throw new Exception('Object must implements IEventable interface');
        }
        if (is_object($this->data)) {
            $this->data->{Event::EVENT_PREFIX . $name}->removeAll();
        } else {
            Event::clear($this->className, $name);
        }
    }

    public static function filterByParent($classes = array(), $parentName, 
                                          $includeAbstact = true)
    {
        $model_classes = array();
        foreach ($classes as $className) {
            $classType = typeOf($className);
            if ($classType->isSubclassOf($parentName) &&
               (!$classType->isAbstract() || $includeAbstact)) {
                    $model_classes[] = $className;
            }
        }
        return $model_classes;
    }

    public static function filterByInterface($classes = array(), $interfaceName, 
                                             $includeAbstact = true)
    {
        $model_classes = array();
        foreach ($classes as $className) {
            $classType = typeOf($className);
            if ($classType->hasInterface($interfaceName) &&
               (!$classType->isAbstract() || $includeAbstact)) {
                    $model_classes[] = $className;
            }
        }
        return $model_classes;
    }

    public static function getByInterface($interfaceName, $includeAbstact = true)
    {
        $classes = get_declared_classes();
        return self::filterByInterface($classes, $interfaceName, $includeAbstact);
    }

    public function callStatic($function, $args)
    {
        if (!is_array($args)) {
            $args = array($args);
        }
        return call_user_func_array(array($this->className, $function), $args);
    }

    public static function getObjectInstance($className, $args = array(), $parent = null, $interfaces = null)
    {
        if (!self::isClassExists($className)) {
            throw new Exception('Invalid class name ' . $className);
        }
        $type = typeOf($className);
        if ($parent != null && !$type->isSubclassOf($parent)) {
            throw new Exception('Class ' . $className . ' must have parent class ' . $parent);
        }
        if ($interfaces != null) {
            if (!is_array($interfaces)) {
                $interfaces = array($interfaces);
            }
            foreach ($interfaces as $interface) {
                if (!$type->hasInterface($interface)) {
                    throw new Exception('Class ' . $className . ' must have interface ' . $interface);
                }
            }
        }
        return $type->createInstance($args);
    }

    public function invoke($method, $args)
    {
        return $this->getMethod($method)->invoke($this->data, $args);
    }

    public function getMethodArgNames($methodName)
    {
        $params = $this->getMethod($methodName)->getParameters();
        $argNames = array();
        foreach ($params as $param) {
            $argNames []= $param->name;
        }
        return $argNames;
    }

    public function getMethod($name)
    {
        return self::getRefType($this->className)->getMethod($name);
    }

    public function getMethods($filter = ReflectionMethod::IS_PUBLIC)
    {
        return self::getRefType($this->className)->getMethods($filter);
    }

    public function hasMethod($methodName)
    {
        return self::getRefType($this->className)->hasMethod($methodName);
    }

    public function hasProperty($propName, $public = true, 
                                           $protected = true, 
                                           $private = false)
    {
        if (!self::getRefType($this->className)->hasProperty($propName)) {
            return false;
        }
        if ($public && $protected && $private) {
            return true;
        }
        $prop = self::getRefType($this->className)->getProperty($propName);
        if ((!$public && $prop->isPublic()) ||
            (!$protected && $prop->isProtected()) ||
            (!$private && $prop->isPrivate())) {
            return false;
        }
        return true;
    }
}
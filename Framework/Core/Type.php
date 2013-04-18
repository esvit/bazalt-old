<?php
/**
 * File with class Type
 *
 * @category   Core
 * @package    Core
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    SVN: $Revision: 155 $
 * @link       http://bazalt-cms.com/
 */

namespace Framework\Core;

/**
 * Клас, що реалізовує роботу з типами даних
 *
 * @category  Core
 * @package   Core
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
     *
     * @return ReflectionClass Клас рефлексії
     */
    protected static function getRefType($type)
    {
        if (!array_key_exists($type, self::$arrTypes)) {
            self::$arrTypes[$type] = new \ReflectionClass($type);
        }
        return self::$arrTypes[$type];
    }

    /**
     * Конструктор
     *
     * @param object|string $mixed Об'єкт або назва класу
     *
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
     *
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
     *
     * @return bool true - якщо клас наслідується від $parentClassName, або false - якщо ні
     */
    public function isSubclassOf($parentClassName)
    {
        $parent = new \ReflectionClass($parentClassName);
        return self::getRefType($this->className)->isSubclassOf($parent);
    }

    /**
     * Перевіряє чи існує заданий клас
     *
     * @param string $className Назва класу 
     *
     * @return bool true - якщо клас існує, або false - якщо ні
     */
    public static function isClassExists($className)
    {
        return class_exists($className);
    }

    /**
     * Повертає список властивостей класу
     * @see http://www.php.net/manual/en/reflectionclass.getproperties.php
     * 
     * @param int $filter Фільтр типу (static, public, protected, private)
     * @return array Масив ReflectionProperty об'єктів
     */
    public function getProperties($filter = \ReflectionMethod::IS_PUBLIC)
    {
        return self::getRefType($this->className)->getProperties($filter);
    }

    /**
     * Створює екземпляр класу
     *
     * @param array $args Масив аргументів конструктора
     *
     * @return mixed Об'єкт класу $this->className
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
     *
     * @return string Назва файлу
     */
    public function getFileName()
    {
        return self::getRefType($this->className)->getFileName();
    }

    /**
     * Повертає події класу
     *
     * @return array Масив подій
     */
    public function getEvents()
    {
        if (!$this->hasInterface('Framework\Core\Interfaces\Eventable')) {
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
     * Додає обробник події
     *
     * @param string   $name     Назва події
     * @param callback $callback Функція обробник
     *
     * @throws Exception
     * @return void
     */
    public function bind($name, $callback)
    {
        if (!$this->hasInterface('Framework\Core\Interfaces\Eventable')) {
            throw new Exception('Object must implements Framework\Core\Interfaces\Eventable interface');
        }
        if (is_object($this->data)) {
            $this->data->{Event::EVENT_PREFIX . $name}->add($callback);
        } else {
            Event::register($this->className, $name, $callback);
        }
    }

    /**
     * Видаляє усі обробники події
     *
     * @param string $name Назва події
     *
     * @throws Exception
     * @return void
     */
    public function unbind($name = null)
    {
        if (!$this->hasInterface('Framework\Core\Interfaces\Eventable')) {
            throw new Exception('Object must implements Framework\Core\Interfaces\Eventable interface');
        }
        if (is_object($this->data)) {
            $this->data->{Event::EVENT_PREFIX . $name}->removeAll();
        } else {
            Event::clear($this->className, $name);
        }
    }

    /**
     * Знаходить всіх нащадків класу $parentName серед масиву $classes
     *
     * @param array  $classes        Масив класів
     * @param string $parentName     Назва батьківського класу
     * @param bool   $includeAbstact Флаг, вказує чи включати до масиву результатів абстрактні класи, по замовчуванню - true
     *
     * @return array Масив класів
     */
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

    /**
     * Знаходить всі класи, які реалізують інтерфейс $interfaceName серед масиву $classes
     *
     * @param array  $classes        Масив класів
     * @param string $interfaceName  Назва інтерфейсу
     * @param bool   $includeAbstact Флаг, вказує чи включати до масиву результатів абстрактні класи, по замовчуванню - true
     *
     * @return array Масив класів
     */
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

    /**
     * Знаходить всі визначені (declared) класи, які реалізують інтерфейс $interfaceName
     *
     * @param string $interfaceName  Назва інтерфейсу
     * @param bool   $includeAbstact Флаг, вказує чи включати до масиву результатів абстрактні класи, по замовчуванню - true
     *
     * @return array Масив класів
     */
    public static function getByInterface($interfaceName, $includeAbstact = true)
    {
        $classes = get_declared_classes();
        return self::filterByInterface($classes, $interfaceName, $includeAbstact);
    }

    /**
     * Виконує метод $function класу $this->className без створення об'єкту класу (Статичний виклик)
     *
     * @param string $function Назва методу
     * @param array  $args     Масив аргументів
     *
     * @return mixed Результат методу
     */
    public function callStatic($function, $args)
    {
        if (!is_array($args)) {
            $args = array($args);
        }
        return call_user_func_array(array($this->className, $function), $args);
    }

    /**
     * Створює і повертає об'єкт класу $className, якщо задано $parent або $interfaces виконує перевірку
     *
     * @param string $className  Назва класу
     * @param array  $args       Масив аргументів, передається в конструктор
     * @param string $parent     Батьківський клас
     * @param array  $interfaces Масив інтерфейсів, які має реалізовувати $className
     *
     * @throws Exception
     * @return mixed Об'єкт класу
     */
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

    /**
     * Викликає метод
     * @see http://www.php.net/manual/en/reflectionmethod.invoke.php
     *
     * @param string $method Назва методу
     * @param array  $args   Масив аргументів
     *
     * @return array mixed Результат
     */
    public function invoke($method, $args)
    {
        return $this->getMethod($method)->invoke($this->data, $args);
    }

    /**
     * Повертає масив імен аргументів методу
     *
     * @param string $methodName Назва методу
     *
     * @return array Масив імен
     */
    public function getMethodArgNames($methodName)
    {
        $params = $this->getMethod($methodName)->getParameters();
        $argNames = array();
        foreach ($params as $param) {
            $argNames []= $param->name;
        }
        return $argNames;
    }

    /**
     * Повертає іняормацію про метод
     * @see http://www.php.net/manual/en/reflectionclass.getmethod.php
     *
     * @param string $name Назва методу
     *
     * @return ReflectionMethod Метод
     */
    public function getMethod($name)
    {
        return self::getRefType($this->className)->getMethod($name);
    }

    /**
     * Повертає масив методів класу
     * @see http://www.php.net/manual/en/reflectionclass.getmethods.php
     *
     * @param int $filter Фільтр типу (static, public, protected, private)
     *
     * @return array Масив методів
     */
    public function getMethods($filter = ReflectionMethod::IS_PUBLIC)
    {
        return self::getRefType($this->className)->getMethods($filter);
    }

    /**
     * Перевіряє чи існує метод $methodName в класу
     * @see www.php.net/manual/en/reflectionclass.hasmethod.php
     * 
     * @param string $methodName  Назва методу
     *
     * @return bool
     */
    public function hasMethod($methodName)
    {
        return self::getRefType($this->className)->hasMethod($methodName);
    }

    /**
     * Перевіряє чи існує поле $propName в класу і чи віповідає воно заданим фільтрам
     * 
     * @param string $propName  Назва поля
     * @param bool   $public    Поле є public
     * @param bool   $protected Поле є protected
     * @param bool   $private   Поле є private
     *
     * @return bool
     */
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
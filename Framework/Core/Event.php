<?php
/**
 * Event class file
 *
 * @category   Core
 * @package    Core
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
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
 * @version   $Revision: 133 $
 * @see       Object, Interfaces\Eventable
 * @property-read string  $ObjectName  Unique name of object
 * @property-read boolean $IsSingleton Singleton flag
 */ 
final class Event extends Object
{
    /**
     * Задається змінній за замовчуванням, щоб цей клас ініціалізував подальшому евент
     *
     * @var string
     * @see EVENT_PREFIX
     */
    const EMPTY_EVENT = 'empty_event';

    /**
     * Префікс змінної, яка містить в собі евент
     *
     * @var string
     */
    const EVENT_PREFIX = 'event';

    /**
     * Префікс метода, який прив'язується до евента при ініціалізації класу
     *
     * @var string
     */
    const METHOD_PREFIX = 'Event_';

    /**
     * Event callbacks
     *
     * @var array
     */
    private static $globalEvents = array();

    /**
     * Event callbacks
     *
     * @var array
     */
    private static $validEvents = array();

    /**
     * Singleton flag
     *
     * @var array
     */
    private $events = array();

    /**
     * Ім'я евента
     *
     * @var string
     */
    private $eventName;

    /**
     * Об'єкт для якого спрацьовує евент
     *
     * @var Interfaces\Eventable
     */
    private $eventableObject;

    /**
     * Останній евент, що був відпрацьований
     *
     * @var Event
     */
    protected static $lastRaisedEvent;

    /**
     * Конструктор
     *
     * @param Interfaces\Eventable $obj       Об'єкт для якого інціалізується евент
     * @param string     $eventName Ім'я евента
     */ 
    protected function __construct(Interfaces\Eventable $obj, $eventName)
    {
        $this->eventableObject = $obj;
        $this->eventName = $eventName;
        parent::__construct();
    }

    /**
     * Повертає останій відпрацьований евент
     *
     * @return Event
     */
    public static function getLastRaisedEvent()
    {
        return self::$lastRaisedEvent;
    }

    /**
     * Повертає ім'я евента
     *
     * @return string
     */
    public function getEventName()
    {
        return $this->eventName;
    }

    /**
     * Повертає об'єкт для якого створено евент
     *
     * @return Interfaces\Eventable
     */
    public function getEventableObject()
    {
        return $this->eventableObject;
    }

    /**
     * Ініцілізує об'єкт який має евенти
     *
     * @param Interfaces\Eventable $object Ініціалізує евенти для об'єкту
     */
    public static function init(Interfaces\Eventable $object)
    {
        $className = get_class($object);

        # Шукає чи вже є якісь зареєстровані евенти для об'єктів цього типу
        $regEvents = array();
        if (isset(self::$globalEvents[$className])) {
            $regEvents = self::$globalEvents[$className];
        }

        $events = self::getEvents($object);
        # Перебирає усі змінні класу для виявлення серед них подій
        foreach ($events as $eventName) {
            $event = new Event($object, $eventName);
            # якщо є зареєстрована подія для цього класу
            if (isset($regEvents[$eventName])) {
                $event->events = $regEvents[$eventName];
            }

            $methodName = self::METHOD_PREFIX . $eventName;
            if (method_exists($object, $methodName)) {
                $event->add(array($object, $methodName));
            }
            $object->{self::EVENT_PREFIX . $eventName} = $event;
        }
    }

    /**
     * Ініціалізує евент у об'єкту з вказаним ім'ям
     *
     * @param  Interfaces\Eventable $object    Об'єкт для якого ініціалізується евент
     * @param  string     $eventName Ім'я евента
     * @return Event
     */ 
    public static function initEvent(Interfaces\Eventable $object, $eventName)
    {
        $className = get_class($object);

        # Шукає чи вже є якісь зареєстровані евенти для об'єктів цього типу
        $regEvents = array();
        if (isset(self::$globalEvents[$className])) {
            $regEvents = self::$globalEvents[$className];
        }

        $event = new Event($object, $eventName);

        # якщо є зареєстрована подія для цього класу
        if (isset($regEvents[$eventName])) {
            $event->events = $regEvents[$eventName];
        }

        $methodName = self::METHOD_PREFIX . $eventName;
        if (method_exists($object, $methodName)) {
            $event->add(array($object, $methodName));
        }
        $object->{self::EVENT_PREFIX . $eventName} = $event;
        return $event;
    }

    /**
     * Повертає усі евент об'єкту
     *
     * @param  Interfaces\Eventable $object Об'єкт з ініціалізованими евентами
     * @return array
     */ 
    public static function getEvents(Interfaces\Eventable $object)
    {
        $className = get_class($object);
        if (!isset(self::$validEvents[$className])) {
            $classType = typeOf($object);
            self::$validEvents[$className] = $classType->getEvents();
        }
        return self::$validEvents[$className];
    }

    /**
     * Повертає евент об'єкту, якщо такого евенту немає то null
     *
     * @param  Interfaces\Eventable $object    Об'єкт з ініціалізованими евентами
     * @param  string     $eventName Ім'я евенту
     * @return Event|null
     */
    public static function get(Interfaces\Eventable $object, $eventName)
    {
        $value = $object->{self::EVENT_PREFIX . $eventName};
        if ($value == self::EMPTY_EVENT) {
            self::initEvent($object, $eventName);
        }
        $value = $object->{self::EVENT_PREFIX . $eventName};
        if (!self::isValid($object, $eventName)) {
            return null;
        }
        return $object->{self::EVENT_PREFIX . $eventName};
    }

    /**
     * Перевіряє чи валідний евент
     *
     * @param  Interfaces\Eventable $object    Об'єкт з ініціалізованими евентами
     * @param  string     $eventName Ім'я евенту
     * @return boolean
     */
    public static function isValid(Interfaces\Eventable $object, $eventName)
    {
        $events = self::getEvents($object);
        return in_array($eventName, $events);
    }

    /**
     * Запускає подію
     *
     * @param array $args Аргументи події
     */
    public function raise($args)
    {
        foreach ($this->events as $event) {
            self::$lastRaisedEvent = $this;
            call_user_func_array($event, $args);
        }
    }

    /**
     * Додає обробник події
     *
     * @param callback $callback Функція
     * @return boolean
     */
    public function add($callback)
    {
        $key = (count($callback) == 1) ? $callback[0] : ((is_string($callback[0]) ? $callback[0] : spl_object_hash($callback[0])) . '::' . $callback[1]);
        if (array_key_exists($key, $this->events)) {
            return false;
        }
        $this->events [$key]= $callback;
        return true;
    }

    /**
     * Видаляє усі обробники події
     */
    public function removeAll()
    {
        /*if (in_array($callback, $this->events)) {
            return false;
        }*/
        $this->events = array();
    }

    /**
     * Видаляє обробник події
     *
     * @param  callback $callback Функція 
     * @throws Exception_Event    Якщо такий обробник не знайдено у цьому евенті
     */
    public function remove($callback)
    {
        $key = (count($callback) == 1) ? $callback[0] : ((is_string($callback[0]) ? $callback[0] : spl_object_hash($callback[0])) . '::' . $callback[1]);

        if (!isset($this->events[$key])) {
            throw new Exception_Event('Callback for event not found');
        }
        unset($this->events[$key]);
    }

    /**
     * Реєструє глобальний евент
     *
     * @param  string   $objName  Ім'я об'єкту
     * @param  string   $name     Ім'я події
     * @param  callback $callback Функція
     * @throws \Exception
     * @return boolean
     */
    public static function register($objName, $name, $callback)
    {
        if (!is_callable($callback)) {
            throw new \Exception('Invalid callback');
        }
        $key = (count($callback) == 1) ? $callback[0] : ((is_string($callback[0]) ? $callback[0] : spl_object_hash($callback[0])) . '::' . $callback[1]);
        if (!isset(self::$globalEvents[$objName][$name])) {
            // Create an empty event if it is not yet defined
            self::$globalEvents[$objName][$name] = array();
        } elseif (in_array($key, self::$globalEvents[$objName][$name], true)) {
            // The event already exists
            return false;
        }

        // Add the event
        self::$globalEvents[$objName][$name][] = $callback;
        return true;
    }

    /**
     * Запускає глобальний евент
     *
     * @param  string   $objName  Ім'я об'єкту
     * @param  string   $name     Ім'я події
     * @param  array    $args     аргументи
     * @return null
     */
    public static function trigger($objName, $name, $args)
    {
        if (!array_key_exists($objName, self::$globalEvents) || !array_key_exists($name, self::$globalEvents[$objName])) {
            return null;
        }
        $events = self::$globalEvents[$objName][$name];

        foreach ($events as $event) {
            call_user_func_array($event, $args);
        }
    }

    /**
     * Очищає усі зареєстровані події
     *
     * @param $objName
     * @param $name
     * @param $callback
     * @throws \Exception
     * @return void
     * @internal param $string Назва класу
     */
    public static function unregister($objName, $name, $callback)
    {
        if (!array_key_exists($name, self::$globalEvents[$objName])) {
            return;
        }
        $key = (count($callback) == 1) ? $callback[0] : ((is_string($callback[0]) ? $callback[0] : spl_object_hash($callback[0])) . '::' . $callback[1]);

        if (!isset(self::$globalEvents[$objName][$name][$key])) {
            throw new \Exception('Callback for event not found');
        }
        unset(self::$globalEvents[$objName][$name][$key]);
    }

    /**
     * Очищає усі callback-функції для події певного об'єкту
     *
     * @param string Назва класу
     * @param string Ім'я події
     */
    public static function clear($objName, $name)
    {
        self::$globalEvents[$objName][$name] = array();
    }

    /**
     * Очищає усі зареєстровані події
     */
    public static function clearAll()
    {
        self::$globalEvents = array();
    }
}
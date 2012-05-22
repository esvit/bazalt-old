<?php
/**
 * Element.php
 *
 * @category   Html
 * @package    BAZALT
 * @subpackage System
 * @copyright  2011 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */

/**
 * Html_Element
 *
 * @category   Html
 * @package    BAZALT
 * @subpackage System
 * @copyright  2011 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */
abstract class Html_Element extends Object implements IEventable
{
    /**
     * Список усіх ідентифікаторів
     */
    protected static $ids = array();

    protected static $idForceAppendIndex = 0;

    /**
     * Element id
     */
    protected $id = null;

    /**
     * Element name
     */
    protected $name = null;

    protected $view = null;

    /**
     * Valid element attributes
     */
    protected $validAttributes = array(
        'accesskey' => 'string',            // Позволяет получить доступ к элементу с помощью заданного сочетания клавиш.
        'class'     => 'string',            // Определяет имя класса, которое позволяет связать тег со стилевым оформлением.
        'dir'       => array('rtl', 'ltr'), // Задает направление и отображение текста — слева направо или справа налево.
        'id'        => 'string',            // Указывает имя стилевого идентификатора.
        'lang'      => 'string',            // Браузер использует значение параметра для правильного отображения некоторых национальных символов.
        'style'     => 'string',            // Применяется для определения стиля элемента с помощью правил CSS.
        'tabindex'  => 'int',               // Устанавливает порядок получения фокуса при переходе между элементами с помощью клавиши Tab.
        'title'     => 'string'             // Описывает содержимое элемента в виде всплывающей подсказки.
    );

    protected $visibleAttributes = array(
        'accesskey' => true,
        'class'     => true,
        'dir'       => true,
        'id'        => true,
        'lang'      => true,
        'style'     => true,
        'tabindex'  => true,
        'title'     => true
    );

    /**
     * Element attributes
     */
    protected $attributes = array();

    /**
     * Class constructor, sets default attributes
     *
     * @param string $name       Element name
     * @param mixed  $attributes Array of attribute 'name' => 'value' pairs or HTML attribute string
     */
    public function __construct($name, $attributes = array())
    {
        if (empty($name)) {
            throw new Html_Exception_Element('Element name cant be empty');
        }
        if (preg_match('/[^0-9A-Za-z_]/', $name)) {
            throw new Html_Exception_Element('FormElement name "' . $name . '" has invalid symbols (allow only 0-9, A-Z, a-z and "_")');
        }
        $this->name = $name;

        $this->attributes = $attributes;
    }

    private static function _getTemplatesFolder()
    {
        return array('Html' => dirname(__FILE__) . '/templates');
    }

    public function view($view = null)
    {
        if ($view === null) {
            if (!$this->view) {
                $this->view = Html_Form::getView();
            }
            return $this->view;
        }
        $folders = $view->getFolders();
        $folders = array_merge(self::_getTemplatesFolder(), $folders);
        $view->setFolders($folders);
        $this->view = $view;
        return $this;
    }

    public function getValidAttributes()
    {
        return $this->validAttributes;
    }

    protected function prependsName()
    {
        return strlen($this->name) > 0;
    }

    public function validAttribute($attr, $allowValues = 'string', $visible = true)
    {
        $attr = strToLower($attr);
        $this->validAttributes[$attr] = $allowValues;
        $this->visibleAttributes[$attr] = $visible;
    }

    public function invalidAttribute($attr)
    {
        $attr = strToLower($attr);
        if (isset($this->validAttributes[$attr])) {
            unset($this->validAttributes[$attr]);
            unset($this->visibleAttributes[$attr]);
        }
    }

    public function name($name = false)
    {
        if ($name !== false) {
            $this->name = $name;
            return $this;
        }
        return $this->name;
    }

    public function id($id = false)
    {
        if ($id !== false) {
            $this->id = $id;
            return $this;
        }
        if (!$this->id && ($name = $this->name())) {
            $this->id = self::generateId($name);
        }
        return $this->id;
    }

    /**
     * Клонування елементу
     */
    public function __clone()
    {
        if (!empty($this->id)) {
            $this->id = self::generateId($this->name());
        }
    }

    /**
     * Generate html for element
     */
    abstract public function toString();

    public function __toString()
    {
        return $this->toString();
    }

    public function getAttributesString()
    {
        $attrs = $this->getValidAttributes();

        $this->attributes['id'] = $this->id();

        $name = $this->name();
        if (!empty($name)) {
            $this->attributes['name'] = $name;
        }

        $res = array();
        foreach ($attrs as $attr => $rule) {
            if ($this->visibleAttributes[$attr]) {
                $value = $this->{$attr}();
                if (!empty($value)) {
                    $res []= $attr . '="' . htmlSpecialChars($value, ENT_QUOTES, 'UTF-8') . '"';
                }
            }
        }

        return implode(' ', $res);
    }

    public function css($css = null)
    {
        if ($css !== null) {
            $this->class(implode(' ', $css));
            return $this;
        }
        return explode(' ', $this->class());
    }

    /**
     * Checks whether the element has given CSS class
     *
     * @param string Class name
     * @return bool
     */
    public function hasClass($class)
    {
        return in_array($class, $this->css());
    }

    /**
     * Adds the given CSS class(es) to the element
     *
     * @param string|array Class name, multiple class names separated by
     *                     whitespace, array of class names
     * @return Html_Element
     */
    public function addClass($class)
    {
        if (!is_array($class)) {
            $class = preg_split('/\s+/', $class, null, PREG_SPLIT_NO_EMPTY);
        }
        $classes = $this->css();
        foreach ($class as $c) {
            if (!in_array($c, $classes)) {
                $classes []= $c;
            }
        }
        $this->css($classes);
        return $this;
    }

    /**
     * Removes the given CSS class(es) from the element
     *
     * @param  string|array Class name, multiple class names separated by
     *                      whitespace, array of class names
     * @return Html_Element
     */
    public function removeClass($class)
    {
        if (!is_array($class)) {
            $class = preg_split('/\s+/', $class, null, PREG_SPLIT_NO_EMPTY);
        }
        $classes = $this->css();
        $classes = array_diff($classes, $class);
        $this->css($classes);
        return $this;
    }

    /**
     * Генерує унікальний ідентифікатор для елементу
     *
     * Called when an element is created without explicitly given id
     *
     * @param  string Ім'я елементу
     * @return string Сгенерований ідентифікатор елементу
     */
    public static function generateId($elementName = '')
    {
        $stop      =  !self::$idForceAppendIndex;
        $tokens    =  strlen($elementName)
                      ? explode('[', str_replace(']', '', $elementName))
                      : ($stop ? array('bzauto', '') : array('bzauto'));
        $container =& self::$ids;
        $id        =  '';

        do {
            $token = array_shift($tokens);
            // Handle the 'array[]' names
            if ($token === '') {
                if (empty($container)) {
                    $token = 0;
                } else {
                    $keys  = array_keys($container);
                    $token = end($keys);
                    if (!is_numeric($token)) {
                        $token = 0;
                    }
                    while (isset($container[$token])) {
                        $token++;
                    }
                }
            }
            $id .= '_' . $token;
            if (!isset($container[$token])) {
                $container[$token] = array();
            } elseif (empty($tokens) && $stop) { // Handle duplicate names when not having mandatory indexes
                $tokens[] = '';
            }
            if (empty($tokens) && !$stop) { // Handle mandatory indexes
                $tokens[] = '';
                $stop     = true;
            }
            $container =& $container[$token];
        } while (!empty($tokens));

        return substr($id, 1); // remove _
    }

    protected function validateAttribute($name, $value, $rule)
    {
        if (is_array($rule)) {
            if (!in_array($value, $rule)) {
                return false;
            }
            return $value;
        }
        switch ($rule) {
        case 'int':
            if (!is_numeric($value)) {
                return false;
            }
            return (int)$value;
        case 'boolean':
            $value = strToLower($value);
            if ($value == 1 || $value == 'true' || $value == 'on') {
                return $name;
            } else if ($value == 0 || $value == 'false' || $value == 'off') {
                return null;
            }
            return false;
        default:
        case 'string':
            if (!is_string($value)) {
                return false;
            }
            return $value;
        case 'object':
            if (!is_object($value)) {
                return false;
            }
            return $value;
        case 'array':
            if (!is_array($value)) {
                return false;
            }
            return $value;
        case 'mixed':
            return $value;
        }
        throw new Html_Exception_Attribute('Invalid attribute type');
    }

    public function __call($origName, $args = array())
    {
        $name = strToLower($origName);
        $attrs = $this->getValidAttributes();
        if (!array_key_exists($name, $attrs)) {
            // if event
            $eventName = Event::EVENT_PREFIX . $origName;
            if (property_exists($this, $eventName) && $this instanceof IEventable) {
                return parent::__call($origName, $args);
            }
            throw new Html_Exception_Attribute('Invalid element attribute "' . $name . '" on element "' . get_class($this) . '"');
        }
        $rule = $attrs[$name];
        if (count($args) > 0) {
            $value = $args[0];
            if ($name != 'value' && ($value = $this->validateAttribute($name, $value, $rule)) === false) {//ignore value attribute
                throw new Html_Exception_Attribute('Invalid value "' . print_r($args[0], true) . '" for attribute "' . $name . '"');
            }
            if ($value === null) {
                unset($this->attributes[$name]);
            } else {
                $this->attributes[$name] = $value;
            }
            return $this;
        }
        if (isset($this->attributes[$name])) {
            if ($rule == 'boolean') {
                return (isset($this->attributes[$name]) && $this->attributes[$name] == $name);
            }
            return $this->attributes[$name];
        }
        return null;
    }
}
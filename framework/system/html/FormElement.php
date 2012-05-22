<?php
/**
 * FormElement.php
 *
 * @category   Html
 * @package    BAZALT
 * @subpackage System
 * @copyright  2011 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */

/**
 * Html_FormElement
 *
 * @category   Html
 * @package    BAZALT
 * @subpackage System
 * @copyright  2011 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */
abstract class Html_FormElement extends Html_Element
{
    /**
     * Значення
     */
    protected $defaultValue = '';

    /**
     * Унікальне ім'я елементу
     */
    protected $originalName = '';

    /**
     * Форма, на якій знаходиться елемент
     * @var Html_Element_Form
     */
    protected $form = null;

    /**
     * Element containing current
     * @var Html_ContainerElement
     */
    protected $container = null;

    /**
     * Валідатори
     */
    protected $validators = array();

    /**
     * Фільтри
     */
    protected $filters = array();

    /**
     * Помилки
     */
    protected $errors = array();

    /**
     * Показує чи вже проініціалізований елемент
     */
    protected $isInited = false;

    public function __construct($name, $attributes = array())
    {
        parent::__construct($name, $attributes);

        $this->validAttribute('name', 'string'); // Имя поля, предназначено для того, чтобы обработчик формы мог его идентифицировать.

        $this->validAttribute('label',   'string', false); // Бірка елементу
        $this->validAttribute('comment', 'string', false); // Коментар для елементу
        $this->validAttribute('value',   'mixed',  false);
        $this->validAttribute('beforeTemplate', 'string',  false);
        $this->validAttribute('afterTemplate', 'string',  false);
        $this->validAttribute('javascriptTemplate', 'string',  false);
        $this->validAttribute('template', 'string',  false);

        $this->originalName = $name;

        $this->template('default');
        $this->javascriptTemplate('elements/javascript/default');

        $this->initAttributes();
    }

    public function initAttributes()
    {
    }

    /**
     * Додає помилку на елемент
     */
    public function addError($name, $text)
    {
        $this->errors[$name] = $text;
    }

    public function addFilter(Html_Filter_Base $filter)
    {
        $filter->setElement($this);
        $this->filters []= $filter;

        return $this;
    }

    public function removeError($name)
    {
        unset($this->errors[$name]);
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function isRequireField()
    {
        $res = false;
        foreach ($this->validators as $validator) {
            if ($validator instanceof Html_Validator_NonEmpty) {
                $res = true;
            }
        }
        return $res;
    }

    public function removeRequireValidators()
    {
        foreach ($this->validators as $k => $validator) {
            if ($validator instanceof Html_Validator_NonEmpty) {
                unset($this->validators[$k]);
            }
        }
    }

    public function clearValidators()
    {
        $this->validators = array();
        return $this;
    }
    
    public function addValidator($name, $config = array())
    {
        $this->validators []= new $name($this, $config);
        return $this;
    }

    public function addRuleNonEmpty($config = array())
    {
        $validator = new Html_Validator_NonEmpty($this, $config);
        $this->validators []= $validator;
        return $this;
    }

    public function addEmailValidator($config = array())
    {
        $validator = new Html_Validator_Email($this, $config);
        $this->validators []= $validator;
        return $this;
    }

    public function addCallbackValidator($config = array())
    {
        $validator = new Html_Validator_Callback($this, $config);
        $this->validators []= $validator;
        return $this;
    }

    public function addCompareValidator(Html_FormElement $compareEl, $config = array())
    {
        $validator = new Html_Validator_Compare($this, $compareEl, $config);
        $this->validators []= $validator;
        return $this;
    }

    public function validate()
    {
        $this->initElement();
        $result = true;
        foreach ($this->validators as $val) {
            $res = $val->validate($this, $this->form());
            $result &= $res;
        }
        return $result;
    }

    public function container(Html_ContainerElement $container = null)
    {
        if ($container !== null) {
            $this->container = $container;
            return $this;
        }
        return $this->container;
    }

    public function form(Html_Form $form = null)
    {
        if ($form !== null) {
            $this->form = $form;
            return $this;
        }
        return $this->form;
    }

    public function value($value = null)
    {
        if ($value !== null) {
            return parent::value($value);
        }
        $this->initElement();
        $value = parent::value();
        if (!empty($value)) {
            return $value;
        }
        if ($this->container()) {
            $values = $this->container()->dataSource()->values();
        } else {
            $values = $this->dataSource()->values();
        }
        if (isset($values[$this->originalName])) {
            return $values[$this->originalName];
        } else if (!$this->prependsName()) {
            return $values;
        }
        return null;
    }

    public function renderLabel()
    {
        $view = $this->view();
        $view->assign('element', $this);
        return $view->fetch('elements/element-label');
    }

    public function renderError()
    {
        $view = $this->view();
        $view->assign('element', $this);
        return $view->fetch('elements/element-error');
    }

    public function renderComment()
    {
        $view = $this->view();
        $view->assign('element', $this);
        return $view->fetch('elements/element-comment');
    }

    public function toString()
    {
        $view = $this->view();
        $view->assign('element', $this);
        $before = $this->beforeTemplate();
        $after = $this->afterTemplate();
        $str = '';
        if (!empty($before)) {
            $str .= $view->fetch($before);
        }

        $form = $this->form();
        if (!$form) {
            throw new Exception('Form not found');
        }
        $form->registerJavascript($this);

        $str .= $view->fetch($this->template());
        if (!empty($after)) {
            $str .= $view->fetch($after);
        }
        return $str;
    }

    /**
     * Ім'я елементу
     */
    public function name($name = null)
    {
        if ($name !== null) {
            $this->name = $name;
            $this->originalName = $name;
            return $this;
        }
        $name = '';
        if ($this->container() != null && ($cName = $this->container()->name())) {
            $name .= $cName;
        }
        if (!empty($this->name) && $this->prependsName()) {
            $name .= (!empty($name)) ? ('[' . $this->name . ']') : $this->name;
        }
        return $name;
    }

    public function getOriginalName()
    {
        return $this->originalName;
    }
    
    public function defaultValue($value = null)
    {
        if ($value === null) {
            return $this->defaultValue;
        }
        $this->defaultValue = $value;
        if(!$this->form()->isPostBack()) {
            $this->value($value);
        }
        return $this;
    }

    public function toJavascript()
    {
        return '';
    }

    public function initElement()
    {
        if ($this->isInited) {
            return;
        }
        $this->isInited = true;
        //echo 'Init element ' . get_class($this) . ' ' . $this->name() . "\n";
    }

    public function generateJavascript()
    {
        $view = $this->view();
        $view->assign('element', $this);
        return $view->fetch($this->javascriptTemplate());
    }

    public function __ajaxCall($method, $params = array())
    {
        if (!is_array($params)) {
            $params = array();
        }
        return call_user_func_array(array($this, $method), $params);
    }

    public function getAjaxMethodsJs()
    {
        $type = typeOf($this);

        $methods = array();
        $classMethods = $type->getMethods();
        foreach ($classMethods as $method) {
            if (!$this->isValidAjaxMethod($method)) {
                continue;
            }
            $args = array();
            foreach ($method->getParameters() as $i => $parameter) {
                $args[$i] = $parameter->name;
            }
            $name = $method->name;

            $methods[$name] = $args;
        }
        $js = '';
        foreach ($methods as $methodName => $methodArgs) {
            $methodName = substr($methodName, 4);
            $methodArgs []= 'callback';
            $args = implode(',', $methodArgs);
            $js .= sprintf('this.%s = function(%s) { this.ajaxCall("%s", %s); }', $methodName, $args, $methodName, $args) . "\n";
        }
        return $js;
    }

    protected function isValidAjaxMethod($method)
    {
        $name = $method->getName();
        // if method private or user haven't rigths for access
        if (substr($name, 0, 4) != 'ajax') {
            return false;
        }
        $class = $method->getDeclaringClass()->name;
        // disable methods of this and parent class
        return !($class == __CLASS__ || $class == 'Object');
    }
}
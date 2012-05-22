<?php

class Html_Element_Form extends Html_ContainerElement
{
    const DEFAULT_CSS_CLASS     = 'bz-form';

    const DEFAULT_FORM_TYPE     = 'application/x-www-form-urlencoded';

    const MULTIPART_FORM_TYPE   = 'multipart/form-data';

    const PLAIN_FORM_TYPE       = 'text/plain';

    const MAX_VALID_TOKENS      = 5;

    public $eventBeforeFormPost = Event::EMPTY_EVENT;
    
    public $eventAfterFormPost  = Event::EMPTY_EVENT;

    protected $dataBindedObject = null;

    protected $csrfKeyField     = null;

    protected $csrfParamField   = null;

    public function dataSource($dataSource = null)
    {
        if ($dataSource != null) {
            $this->dataSource = $dataSource;
            return $this;
        }
        if ($this->dataSource == null) {
            if (!$this->subform()) {
                $this->dataSource = new Html_DataSource_Post($this);
            } else {
                $this->dataSource = parent::dataSource();
            }
        }
        return $this->dataSource;
    }

    protected function prependsName()
    {
        return true;
    }

    public function error($err)
    {
        if (is_object($err) && $err instanceof Exception) {
            $this->addError($this->name, $err->getMessage());
        } else {
            $this->addError($this->name, $err);
        }
    }

    /**
     * Class constructor, sets default attributes
     *
     * @param    mixed   Array of attribute 'name' => 'value' pairs or HTML attribute string
     */
    public function __construct($name, $attributes = array())
    {
        $this->form = $this;

        parent::__construct($name, $attributes);

        $this->addClass(self::DEFAULT_CSS_CLASS);

        $this->validAttribute('csrf', 'boolean', false);
        $this->validAttribute('subform', 'boolean', false);
        $this->validAttribute('accept-charset'); // Устанавливает кодировку, в которой сервер может принимать и обрабатывать данные
        $this->validAttribute('action');         // Адрес программы или документа, который обрабатывает данные формы. 
        $this->validAttribute('enctype', array(
                self::DEFAULT_FORM_TYPE,
                self::MULTIPART_FORM_TYPE,
                self::PLAIN_FORM_TYPE
            )
        );        // MIME-тип информации формы.
        $this->validAttribute('method', array('get', 'post')); // Метод протокола HTTP.
        //$this->validAttribute('autocomplete');

        // defaults
        $this->method('post');
        if(!isset($attributes['accept-charset'])) {
            $this->attributes['accept-charset'] = 'utf-8';
        }
        $this->enctype(self::DEFAULT_FORM_TYPE);
        if(!isset($attributes['action'])) {
            $this->action(Url::getRequestUrl(true));
        }
        //$this->attributes['autocomplete'] = 'off';
        $this->subform(false);
        $this->csrf(true);
    }

    public function initElement()
    {
        if ($this->isInited) {
            return;
        }
        if ($this->csrf() && !$this->subform()) {
            $this->addCsrfFields();
        }

        parent::initElement();

        $values = $this->dataSource()->values();
        $this->value($values);
    }

    public function setPostBack($isPostBack)
    {
        $this->isPostBack = $isPostBack;
    }
    
    public function isPostBack()
    {
        return $this->dataSource()->isPostBack();//(strToLower($_SERVER['REQUEST_METHOD']) == 'post') && isset($_POST[$this->name]);
    }

    public function save()
    {
        $this->BeforeFormPost($this);
        if ($this->dataBindedObject != null){
            $this->dataBindedObject->save();

            $this->AfterFormPost($this);
        }
    }

    public function validate()
    {
        $this->initElement();

        if ($this->csrf() && !$this->subform()) {
            $values = $this->value();
            $csrf1 = $this->csrfKeyField->value();
            $csrf2 = $this->csrfParamField->value();
            $isValid = $this->isValidCsrfToken($csrf1, $csrf2);

            if ($isValid) {
                $this->invalidateCsrfToken($csrf1);
            }

            $this->csrfKeyField->value($this->csrfKeyField->defaultValue());
            $this->csrfParamField->value($this->csrfParamField->defaultValue());
            $this->setCsrfValues();

            if (!$isValid) {
                $this->addError('crsf', 'Invalid CSRF token found');// . ' ' . $csrf2);
                parent::validate();
                return false;
            }
        }
        return parent::validate();
    }

    public function dataBind(ORM_Record $object)
    {
        $this->dataBindedObject = $object;

        if ($this->isPostBack()) {
            foreach($this->elements as $element) {
                if ($element instanceOf Html_ContainerElement) {
                    $element->dataBind($object);
                }
            }

            $values = $this->value();

            foreach ($this->dataBindedObject->getColumns() as $key => $column) {
                if (isset($this[$key])) {
                    $this->dataBindedObject->{$key} = $this[$key]->value();
                }
            }
        } else {
            foreach ($this->elements as $element) {
                if ($element->prependsName()) {
                    if (isset($this->dataBindedObject->{$element->getOriginalName()})) {
                        $element->value($this->dataBindedObject->{$element->getOriginalName()});
                    }
                }
                if ($element instanceOf Html_ContainerElement) {
                    $element->dataBind($object);
                }
            }
        }
    }

    protected function addCsrfFields()
    {
        $this->csrfKeyField = $this->addElement('hidden', 'csrf1')
                                   ->defaultValue(DataType_Guid::newGuid()->toString());

        $this->csrfParamField = $this->addElement('hidden', 'csrf2')
                                     ->defaultValue(DataType_Guid::newGuid()->toString());

        if (!$this->isPostBack()) {
            $this->setCsrfValues();
        }
    }

    protected function setCsrfValues()
    {
        Session::Singleton()->{$this->csrfKeyField->value()} = $this->csrfParamField->value();

        $tokensName = $this->name() . '_csrf_tokens';
        $tokens = Session::Singleton()->{$tokensName};
        if (!is_array($tokens)) {
            $tokens = array();
        }
        $tokens []= $this->csrfKeyField->value();
        if (count($tokens) > self::MAX_VALID_TOKENS) {
            $t = array_shift($tokens);
            unset(Session::Singleton()->{$t});
        }
        Session::Singleton()->{$tokensName} = $tokens;
    }

    protected function isValidCsrfToken($key, $value)
    {
        $keyValue = Session::Singleton()->{$key};
        return ($keyValue == $value);
    }

    protected function invalidateCsrfToken($token)
    {
        unset(Session::Singleton()->{$token});
    }

    public function csrfFields($attributes = array())
    {
        $str = $this->csrfKeyField->toString();

        $str .= $this->csrfParamField->toString();

        return $str;
    }

    public function begin($attributes = array())
    {
        $this->initElement();
        $this->attributes = array_merge($this->attributes, $attributes);

        $view = Html_Form::getView();
        $view->assign('element', $this);
        return $view->fetch('elements/form');
        
    }

    public function end()
    {
        return '</form>';
    }

    public function toString()
    {
        if (!$this->subform()) {
            $str = $this->begin();
        }

        $str .= parent::toString();

        if (!$this->subform()) {
            $str .= $this->end();
        }
        return $str;
    }
}

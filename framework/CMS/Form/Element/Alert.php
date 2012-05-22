<?php

class CMS_Form_Element_Alert extends Html_Element_Flasher
{
    protected $actions = array();

    const DEFAULT_CSS_CLASS = 'bz-form-element-alert';

    public function __construct($name, $attributes = array())
    {
        parent::__construct($name, $attributes);

        $this->template('elements/alert');

        $this->removeClass(Html_Element_Flasher::DEFAULT_CSS_CLASS);
        $this->addClass(self::DEFAULT_CSS_CLASS);
    }

    public function value($value = null)
    {
        if ($value != null) {
            return $this;
        }
        return CMS_Bazalt::getSecretKey();
    }

    public function addAction($text, $function)
    {
        $this->actions[$text] = $function;
        $this->saveActions();
        return $this;
    }

    public function removeAction($text)
    {
        unset($this->actions[$text]);
        $this->saveActions();
        return $this;
    }

    public function clearActions()
    {
        $this->actions = array();
        $this->saveActions();
        return $this;
    }

    protected function saveActions()
    {
        $sessionField = 'flasher_actions_' . $this->id();
        Session::Singleton()->{$sessionField} = serialize($this->actions);
    }

    public function getActions()
    {
        $sessionField = 'flasher_actions_' . $this->id();
        return unserialize(Session::Singleton()->{$sessionField});
    }

    public static function getCloseAction()
    {
        return 'javascript:$(\'.' . self::DEFAULT_CSS_CLASS . '\').remove(); return false;';
    }
}
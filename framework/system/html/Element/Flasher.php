<?php

class Html_Element_Flasher extends Html_FormElement
{
    const DEFAULT_CSS_CLASS = 'bz-form-flasher';

    public function initAttributes()
    {
        parent::initAttributes();

        $this->validAttribute('type', array('warning', 'error', 'success', 'info'));

        $this->template('elements/flasher');

        $this->addClass(self::DEFAULT_CSS_CLASS);
    }

    public function text($text = null)
    {
        $sessionField = 'flasher_text_' . $this->id();
        if ($text != null) {
            Session::Singleton()->{$sessionField} = $text;
            return $this;
        }
        return Session::Singleton()->{$sessionField};
    }

    public function type($type = null)
    {
        $sessionField = 'flasher_type_' . $this->id();
        if ($type != null) {
            Session::Singleton()->{$sessionField} = $type;
            return $this;
        }
        if (!isset(Session::Singleton()->{$sessionField})) {
            return 'success';
        }
        return Session::Singleton()->{$sessionField};
    }

    public function toString()
    {
        $text = $this->text();

        if (!empty($text)) {
            $str = parent::toString();
            unset(Session::Singleton()->{'flasher_text_' . $this->id()});
            return $str;
        }
        return '';
    }
}
<?php

using('Framework.System.Html');
using('Framework.System.Google.Recaptcha');

class KCaptcha_Form_Captcha extends Html_FormElement
{
    protected $title = null;

    public function __construct()
    {
        parent::__construct('kcaptcha');
    }

    public function getValidAttributes()
    {
        $attrs = parent::getValidAttributes();
        $attrs []= 'type';
        $attrs []= 'value';
        $attrs []= 'name';
        $attrs []= 'autocomplete';
        return $attrs;
    }

    public function title($title = null)
    {
        if ($title != null) {
            $this->title = $title;
            return $this;
        }
        return $this->title;
    }

    public function validate()
    {
        $enteredCaptcha = $this->value();
        $captcha = Session::Singleton()->KCaptcha;

        if ($enteredCaptcha != $captcha) {
            $this->addError($this->name() . __CLASS__, 'Вы неправильно ввели символы на картинке' . $enteredCaptcha . '==' . $captcha);
            $this->form->addError($this->name() . __CLASS__, 'Вы неправильно ввели символы на картинке');
        }
        return ($enteredCaptcha == $captcha);
    }

    public function toString()
    {
        $str = '';

        $this->attributes['autocomplete'] = 'off';
        $this->attributes['type'] = 'text';

        $attrs = $this->getAttributesString();

        $cls = 'bz-form-row';
        if (count($this->errors) > 0) {
            $cls .= ' bz-form-row-has-error';
        }
        $str .= '<div class="' . $cls . '">';
        $str .= $this->renderLabel();

        $str .= '<div id="recaptcha_widget">';
        $str .= '    <input ' . $attrs . ' />';
        $str .= '    <img style="display: block;" id="recaptcha_image" src="' . Mapper::urlFor('KCaptcha') . '" />';
        $str .= '</div>';

        $str .= $this->renderError();
        $str .= '    <div class="spacer"></div>';
        $str .= '</div>';
        return $str;
    }
}
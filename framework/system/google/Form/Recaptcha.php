<?php

using('Framework.System.Html');
using('Framework.System.Google.Recaptcha');

class Google_Form_Recaptcha extends Html_FormElement
{
    protected $title = null;

    public function __construct()
    {
        parent::__construct('recaptcha_response_field');

        $this->id = 'recaptcha_response_field';
    }

    public function getValidAttributes()
    {
        $attrs = parent::getValidAttributes();
        $attrs []= 'type';
        $attrs []= 'value';
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
        BazaltCMS::getComponent('ComGoogleServices');
        $privatekey = ComGoogleServices::getRecaptchaPrivateKey();
        $enteredCaptcha = $this->value();

        $resp = recaptcha_check_answer($privatekey, $_SERVER['REMOTE_ADDR'], $_POST['recaptcha_challenge_field'], $enteredCaptcha);
        if (!$resp->is_valid) {
            $this->addError($this->name() . __CLASS__, 'Вы неправильно ввели символы на картинке');
            $this->form->addError($this->name() . __CLASS__, 'Вы неправильно ввели символы на картинке');
        }
        return $resp->is_valid;
    }

    public function toString()
    {
        $str = '';

        $key = ComGoogleServices::getRecaptchaPublicKey();

        $this->attributes['autocomplete'] = 'off';
        $this->attributes['type'] = 'text';

        $attrs = $this->getAttributesString();

        $cls = 'bz-form-row';
        if (count($this->errors) > 0) {
            $cls .= ' bz-form-row-has-error';
        }
        $str .= '<div class="' . $cls . '">';
        $str .= $this->renderLabel();
        
        $str .= '<script type="text/javascript"> var RecaptchaOptions = { theme : "custom", custom_theme_widget: "recaptcha_widget" }; </script>';

        $str .= '<div id="recaptcha_widget" style="display:none">';
        $str .= '    <input ' . $attrs . ' />';
        $str .= '    <div id="recaptcha_image"></div>';
        $str .= '    <div class="recaptcha_only_if_incorrect_sol" style="color:red">Incorrect please try again</div>';
        //$str .= '    <span class="recaptcha_only_if_image">Enter the words above:</span>';
        $str .= '    <a href="javascript:Recaptcha.reload()">Get another picture</a>';
        $str .= '</div>';

        try {
            $str .= recaptcha_get_html($key);
        } catch (Exception $e) {
            $str .= $e->getMessage();
        }

        $str .= $this->renderError();
        $str .= '    <div class="spacer"></div>';
        $str .= '</div>';
        return $str;
    }
}
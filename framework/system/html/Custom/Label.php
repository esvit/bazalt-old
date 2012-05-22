<?php

class Html_Element_Label extends Html_FormElement
{
    public function getValidAttributes()
    {
        $attrs = parent::getValidAttributes();
        $attrs []= 'value';
        return $attrs;
    }

    public function toString()
    {
        $str = '';

        $this->attributes['value'] = htmlspecialchars($this->value, ENT_QUOTES, 'UTF-8');

        $template = '<div class="{css}">' .
                        '{label}' .
                        '<label {attrs}>{value}</label>' .
                        '{comment}' .
                    '</div>';
        $params['css'] = implode(' ', $css);

        $params['label'] = $this->renderLabel();
        $params['attrs'] = $attrs;
        $params['value'] = $this->value();
        $params['comment'] = $this->renderComment();

        $str .= DataType_String::replaceConstants($template, $params);
        return $str;
    }
}
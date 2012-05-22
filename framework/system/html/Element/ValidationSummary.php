<?php

class Html_Element_ValidationSummary extends Html_FormElement
{
    const DEFAULT_CSS_CLASS = 'bz-form-validationsummary';

    public function initAttributes()
    {
        parent::initAttributes();

        $this->validAttribute('title');

        $this->template('elements/validationsummary');

        $this->addClass(self::DEFAULT_CSS_CLASS);
    }

    public function toString()
    {
        $this->invalidAttribute('title');

        $errors = $this->form->getErrors();

        if (count($errors)>0){
            $view = Html_Form::getView();
            $view->assign('errors', $errors);
            return parent::toString();
        }
        return '';
    }
}

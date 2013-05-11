<?php

class ComEcommerce_Form_Settings extends Admin_Form_BaseSettings
{
    protected $prQuantity = null;

    public function addSettingFormElements()
    {
        $this->prQuantity = $this->addElement('text', 'prQuantity')
                             ->label(__('Products quantity', ComEcommerce::getName()))
                             ->addClass('ui-input');
                             //->comment(__('Enter the email address to which your messages would be forwarded', ComEcommerce::getName()));
    }

    public function setDefaultValue()
    {
        $this->prQuantity->value(CMS_Option::get(ComEcommerce::PRODUCTS_PAGECOUNT_OPTION, '10'));
    }

    public function saveSettings()
    {
        CMS_Option::set(ComEcommerce::PRODUCTS_PAGECOUNT_OPTION, $this->prQuantity->value());
    }
}
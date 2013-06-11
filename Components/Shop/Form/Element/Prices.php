<?php

class ComEcommerce_Form_Element_Prices extends Html_FormElement
{
    protected $prices = array();
    
    public function setPrices($prices)
    {
        $this->prices = $prices;
    }

    public function __construct($name = null, $attributes = array())
    {
        parent::__construct($name, $attributes);
        
        $this->template('elements/products-prices');
        $this->view(CMS_Bazalt::getComponent('ComEcommerce')->getView());
        $this->decorator(new Html_Decorator_Empty());
    }
    
    public function toString()
    {
        $view = $this->view();
        $accountTypes = ComPay_Model_AccountType::getAccountTypes();
        foreach($accountTypes as $accountType) {
            foreach($this->prices as $price) {
                if($accountType->id == $price->account_id) {
                    $accountType->price = $price->price;
                }
            }
        }
        $view->assign('accountTypes', $accountTypes);
        return parent::toString();
    }
    
    public function save()
    {
        $values = $this->value();
        $ids = array();
        foreach($values as $id => $price) {
            $ref = ComEcommerce_Model_ProductsPrices::getByParams(array(
                'product_id' => $this->form->DataBindedObject->id,
                'account_id' => $id
            ));
            if(!$ref) {
                $ref = new ComEcommerce_Model_ProductsPrices();
                $ref->product_id = $this->form->DataBindedObject->id;
                $ref->account_id = $id;
            }
            $ref->price = $price;
            $ref->save();
        }
    }
}
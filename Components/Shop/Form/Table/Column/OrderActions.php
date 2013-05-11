<?php

class ComEcommerce_Form_Table_Column_OrderActions extends CMS_Form_Element_TableColumn
{
    public function __construct($name)
    {
        parent::__construct($name);

        $this->columnTemplate('table/column/order-actions');
    }
    
    public function ajaxDelete($ids)
    {
        if(!is_array($ids)) {
            $ids = array($ids);
        }
        return ORM::delete('ComEcommerce_Model_Order')
                    ->whereIn('id', $ids)
                    ->exec();
    }
}
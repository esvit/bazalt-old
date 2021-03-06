<?php

class ComEcommerce_Form_Table_Brands extends CMS_Form_Element_Table
{
    public function getUrl($page)
    {
        return CMS_Mapper::urlFor('ComEcommerce.BrandsList', array('?page' => $page));
    }

    public function initColumns()
    {
        $this->view(CMS_Bazalt::getComponent('ComEcommerce')->View);

        $this->addColumn(new CMS_Form_Element_Column_Checkbox('id'));
        $this->addColumn('title', __('Title', ComEcommerce::getName()))
             ->columnTemplate('table/column/brand-title')
             ->canSorting(true);
    }
}
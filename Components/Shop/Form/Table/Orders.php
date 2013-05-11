<?php

class ComEcommerce_Form_Table_Orders extends CMS_Form_Element_Table
{
    public function getUrl($page)
    {
        return CMS_Mapper::urlFor('ComEcommerce.Orders', array('?page' => $page));
    }

    public function ajaxDelete($ids)
    {
        return ComEcommerce_Model_Order::deleteByIds($ids);
    }

    public function initElement()
    {
        parent::initElement();

        $this->addMassAction(
            $this,
            'ajaxDelete',
            __('Delete', ComEcommerce::getName()),
            __('Are you realy want to delete selected records ?', ComEcommerce::getName())
        );
    }

    public function initColumns()
    {
        $this->view(CMS_Bazalt::getComponent('ComEcommerce')->View);

        $this->addColumn(new CMS_Form_Element_Column_Checkbox('id'));
        $this->addColumn(new CMS_Form_Element_Column_Card('name', array(
            'ComEcommerce.OrderView',
            array(
                'id' => 'id'
            ),
            'descField' => 'comment',
            // 'imageField' => 'image',
            // 'imageSize' => 'ComBms.AdminBannerThumbnail'
        )), __('Name', ComEcommerce::getName()))
            ->canSorting(true);

        $this->addColumn('price', __('Price', ComEcommerce::getName()));
        $this->addColumn('phone', __('Phone', ComEcommerce::getName()));

        $this->addColumn(new CMS_Form_Element_Column_Date('created_at'), __('Date of creation', ComEcommerce::getName()))
             ->canSorting(true);

        $this->addColumn('address', __('Address', ComEcommerce::getName()));

        $this->addColumn(new ComEcommerce_Form_Table_Column_OrderActions('actions'), __('Actions', ComEcommerce::getName()))
             ->width(100);

        $this->addColumn(new CMS_Form_Element_Column_Actions(array(
            'edit' => array(
                'ComEcommerce.OrderView',
                array(
                    'id' => 'id'
                ),
                'iconClass' => 'icon-pencil',
                'title' => __('Edit', ComEcommerce::getName())
            ),
            'delete'
        )), __('Actions', ComEcommerce::getName()))
            ->width(100);
    }
}
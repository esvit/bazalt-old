<?php

class ComEcommerce_Form_Table_Products extends CMS_Form_Element_Table
{
    public function getUrl($page)
    {
        return CMS_Mapper::urlFor('ComEcommerce.ProductsList', array('?page' => $page));
    }

    public function ajaxDelete($ids)
    {
        return ComEcommerce_Model_Product::deleteByIds($ids);
    }

    public function ajaxLoadByCategory($categoryId)
    {
        $category = ComEcommerce_Model_Category::getByIdAndCompanyId($categoryId);
        if (!$category) {
            throw new Exception('Category not found');
        }
        $this->view()->assign('selectedId', $categoryId);
        $this->collection(ComEcommerce_Model_Product::getProductsCollection($category))
              ->pager('ComEcommerce.ProductsList');

        return $this->toString();
    }

    public function ajaxGetPageCategory($categoryId, $page, $sortColumn, $sortDirection)
    {
        $category = ComEcommerce_Model_Category::getByIdAndCompanyId($categoryId);
        if (!$category) {
            throw new Exception('Category not found');
        }
        $this->collection(ComEcommerce_Model_Product::getProductsCollection($category))
              ->pager('ComEcommerce.ProductsList');

        $this->page = $page;
        $this->sortColumn = $sortColumn;
        $this->sortDirection = $sortDirection;
        return $this->toString();
    }

    public function initColumns()
    {
        $this->view(CMS_Bazalt::getComponent('ComEcommerce')->View);

        $this->addColumn(new CMS_Form_Element_Column_Checkbox('id'));
        $this->addColumn('title', __('Title', ComEcommerce::getName()))
             ->columnTemplate('table/column/product-title')
             ->canSorting(true);
        $this->addColumn('price', __('Price', ComEcommerce::getName()))
             ->columnTemplate('table/column/product-price')
             ->width(50);
        $this->addColumn('count', __('Amount in the stock', ComEcommerce::getName()))
             ->width(50);
        $this->addColumn(new CMS_Form_Element_Column_Publish('publish', 'ComEcommerce_Model_Product'), __('Publish', ComEcommerce::getName()));

        $this->addColumn(new CMS_Form_Element_Column_Actions(array(
            'edit' => array(
                'ComEcommerce.ProductEdit', 
                array(
                    'id' => 'id'
                ),
                'iconClass' => 'icon-pencil',
                'title' => __('Edit', ComEcommerce::getName())
            ),
            'clone' => array(
                'ComEcommerce.ProductClone', 
                array(
                    'id' => 'id'
                ),
                'iconClass' => 'icon-plus',
                'title' => __('Clone', ComEcommerce::getName())
            ),
            'delete'
        )), __('Actions', ComEcommerce::getName()))
            ->width(120);
    }
}
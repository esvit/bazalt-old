<?php

namespace Components\Shop\Model\Base;

abstract class ProductRefGroup extends \Framework\CMS\ORM\Record
{
    const TABLE_NAME = 'com_shop_products_categories';

    const MODEL_NAME = 'Components\Shop\Model\ProductRefCategory';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('product_id', 'PU:int(10)');
        $this->hasColumn('category_id', 'PU:int(10)');
    }

    public function initRelations()
    {
    }
}
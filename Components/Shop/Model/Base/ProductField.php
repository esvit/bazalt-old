<?php

namespace Components\Shop\Model\Base;

/**
 * @property-read int $product_id
 * @property-read int $field_id
 * @property-read varchar $value
 */
abstract class ProductField extends \Framework\CMS\ORM\Record
{
    const TABLE_NAME = 'com_shop_products_fields';

    const MODEL_NAME = 'Components\Shop\Model\ProductField';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('product_id', 'PU:int(10)');
        $this->hasColumn('field_id', 'PU:int(10)');
        $this->hasColumn('value', 'P:varchar(255)');
    }

    public function initRelations()
    {
    }
}
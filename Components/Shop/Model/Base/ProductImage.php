<?php
/**
 * @property-read int $product_id
 * @property-read int $field_id
 * @property-read varchar $value
 */
namespace Components\Shop\Model\Base;

abstract class ProductImage extends \Framework\CMS\ORM\Record
{
    const TABLE_NAME = 'com_shop_products_images';

    const MODEL_NAME = 'Components\Shop\Model\ProductImage';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PUA:int(10)');
        $this->hasColumn('product_id', 'U:int(10)');
        $this->hasColumn('url', 'varchar(255)');
        $this->hasColumn('order', 'U:int(10)');
    }

    public function initRelations()
    {
        $this->hasRelation('Product', new \Bazalt\ORM\Relation\One2One('Components\Shop\Model\Product', 'product_id',  'id'));
    }
    
}
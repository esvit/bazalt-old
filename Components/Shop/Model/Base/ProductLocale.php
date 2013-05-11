<?php

/**
 * @property-read int $id
 * @property-read int $lang_id
 * @property-read varchar $title
 * @property-read text $description
 * @property-read tinyint $completed
 */
namespace Components\Shop\Model\Base;

abstract class ProductLocale extends \Framework\CMS\ORM\Record
{
    const TABLE_NAME = 'com_shop_products_locale';

    const MODEL_NAME = 'Components\Shop\Model\ProductLocale';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PU:int(10)|0');
        $this->hasColumn('lang_id', 'PU:int(10)');
        $this->hasColumn('title', 'N:varchar(255)');
        $this->hasColumn('description', 'N:text');
        $this->hasColumn('completed', 'U:tinyint(1)|0');
    }

}
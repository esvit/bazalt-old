<?php

namespace Components\Shop\Model\Base;

abstract class Shop extends \Framework\CMS\ORM\Record
{
    const TABLE_NAME = 'com_shop_shops';

    const MODEL_NAME = 'Components\Shop\Model\Shop';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('user_id', 'PU:int(10)');
        $this->hasColumn('product_id', 'PU:int(10)');
        $this->hasColumn('type', 'P:varchar(255)');
        $this->hasColumn('created_at', 'datetime');
    }

    public function initRelations()
    {
    }

    public function initPlugins()
    {
    }
}
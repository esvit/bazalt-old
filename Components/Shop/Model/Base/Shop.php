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
        $this->hasColumn('id', 'PUA:int(10)');
        $this->hasColumn('title', 'N:varchar(255)');
    }

    public function initRelations()
    {
    }

    public function initPlugins()
    {
    }
}
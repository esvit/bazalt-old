<?php

namespace Components\Shop\Model\Base;

abstract class Brand extends \Framework\CMS\ORM\Record
{
    const TABLE_NAME = 'com_shop_brands';

    const MODEL_NAME = 'Components\Shop\Model\Brand';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PUA:int(10)');
        $this->hasColumn('shop_id', 'U:int(10)');
        $this->hasColumn('lft', 'U:int(10)');
        $this->hasColumn('rgt', 'U:int(10)');
        $this->hasColumn('title', 'N:varchar(255)');
        $this->hasColumn('description', 'N:text');
        $this->hasColumn('logo', 'N:varchar(255)');
    }

    public function initRelations()
    {
        $this->hasRelation('Elements', new \Bazalt\ORM\Relation\NestedSet('Components\Ecommerce\Model\Brands', 'site_id'));
    
    }

    public function initPlugins()
    {
        $this->hasPlugin('Bazalt\ORM\Plugin\Timestampable', ['created' => 'created_at', 'updated' => 'updated_at']);
    }
    
}
<?php

namespace Components\Shop\Model\Base;

abstract class Category extends \Framework\CMS\ORM\Record
{
    const TABLE_NAME = 'com_shop_categories';

    const MODEL_NAME = 'Components\Shop\Model\Category';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PUA:int(10)');
        $this->hasColumn('shop_id', 'UN:int(10)');
        $this->hasColumn('image', 'varchar(255)');
        $this->hasColumn('title', 'varchar(255)');
        $this->hasColumn('description', 'mediumtext');
        $this->hasColumn('alias', 'varchar(255)');
        $this->hasColumn('is_published', 'U:tinyint(1)');
        $this->hasColumn('lft', 'U:tinyint(1)');
        $this->hasColumn('rgt', 'U:tinyint(1)');
    }

    public function initRelations()
    {
        $this->hasRelation('Elements', new \Bazalt\ORM\Relation\NestedSet('Components\Shop\Model\Category', 'shop_id'));
        $this->hasRelation('PublicElements', new \Bazalt\ORM\Relation\NestedSet('Components\Shop\Model\Category', 'shop_id', null, array('is_published' => 1)));
        // $this->hasRelation('Root', new \Bazalt\ORM\Relation\One2One('\Framework\CMS\Model\CategoryRoot', 'root_id', 'id'));
        
        $this->hasRelation('Products', new \Bazalt\ORM\Relation\Many2Many('Components\Shop\Model\Product', 'category_id', 'Components\Shop\Model\ProductsCategories', 'product_id'));
    }

    public function initPlugins()
    {
        $this->hasPlugin('Framework\CMS\ORM\Localizable', ['title', 'description']);
    }
}
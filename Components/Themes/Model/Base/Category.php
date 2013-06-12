<?php

namespace Components\News\Model\Base;

abstract class Category extends \Framework\CMS\ORM\Record
{
    const TABLE_NAME = 'com_news_categories';

    const MODEL_NAME = 'Components\News\Model\Category';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PUA:int(10)');
        $this->hasColumn('site_id', 'U:int(10)');
        $this->hasColumn('image', 'varchar(255)');
        $this->hasColumn('title', 'varchar(255)');
        $this->hasColumn('description', 'mediumtext');
        $this->hasColumn('alias', 'varchar(255)');
        $this->hasColumn('is_hidden', 'U:tinyint(1)|0');
        $this->hasColumn('is_publish', 'U:tinyint(1)');
    }

    public function initRelations()
    {
        $this->hasRelation('Elements', new \Bazalt\ORM\Relation\NestedSet('Components\News\Model\Category', 'site_id'));
        $this->hasRelation('PublicElements', new \Bazalt\ORM\Relation\NestedSet('Components\News\Model\Category', 'site_id', null, array('is_hidden' => '0', 'is_publish' => 1)));
    }

    public function initPlugins()
    {
        $this->hasPlugin('Framework\CMS\ORM\Localizable', ['title', 'description']);
    }
}
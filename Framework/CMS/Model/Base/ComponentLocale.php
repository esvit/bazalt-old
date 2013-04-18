<?php

namespace Framework\CMS\Model\Base;

abstract class ComponentLocale extends \Framework\CMS\ORM\Record
{
    const TABLE_NAME = "cms_components_locale";

    const MODEL_NAME = "Framework\CMS\Model\ComponentLocale";

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PU:int(10)|0');
        $this->hasColumn('lang_id', 'PU:varchar(2)');
        $this->hasColumn('title', 'N:varchar(255)');
        $this->hasColumn('description', 'N:text');
        $this->hasColumn('completed', 'U:tinyint(4)|0');
    }

    public function initRelations()
    {
    }
}
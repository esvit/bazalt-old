<?php

namespace Framework\CMS\Model\Base;

abstract class Theme extends \Framework\CMS\ORM\Record
{
    const TABLE_NAME = 'cms_themes';

    const MODEL_NAME = 'Framework\CMS\Model\Theme';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'P:varchar(50)');
        $this->hasColumn('settings', 'text');
        $this->hasColumn('is_active', 'U:tinyint(3)|0');
        $this->hasColumn('is_hidden', 'U:tinyint(3)|0');
    }

    public function initRelations()
    {
    }

    public function initPlugins()
    {
        $this->hasPlugin('Bazalt\ORM\Plugin\Serializable', 'settings');
    }
}
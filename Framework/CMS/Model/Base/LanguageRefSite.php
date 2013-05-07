<?php

namespace Framework\CMS\Model\Base;

abstract class LanguageRefSite extends \Framework\CMS\ORM\Record
{
    const TABLE_NAME = 'cms_languages_ref_sites';

    const MODEL_NAME = 'Framework\CMS\Model\LanguageRefSite';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('language_id', 'PU:int(10)');
        $this->hasColumn('site_id', 'PU:int(10)');
        $this->hasColumn('is_active', 'U:tinyint(3)|0');
    }

    public function initRelations()
    {
    }

    public function initPlugins()
    {
    }
}
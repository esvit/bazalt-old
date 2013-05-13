<?php

namespace Components\News\Model\Base;

abstract class Region extends \Framework\CMS\ORM\Record
{
    const TABLE_NAME = 'com_news_regions';

    const MODEL_NAME = 'Components\News\Model\Region';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PA:int(10)');
        $this->hasColumn('title', 'N:varchar(255)');
        $this->hasColumn('title_in_case', 'N:varchar(255)');
        $this->hasColumn('alias', 'N:varchar(255)');
        $this->hasColumn('keywords', 'N:text');
    }

    public function initRelations()
    {
    }
}
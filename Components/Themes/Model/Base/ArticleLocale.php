<?php

namespace Components\News\Model\Base;

abstract class ArticleLocale extends \Framework\CMS\ORM\Record
{
    const TABLE_NAME = 'com_news_news_locale';

    const MODEL_NAME = 'Components\News\Model\ArticleLocale';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PU:int(10)|0');
        $this->hasColumn('lang_id', 'PU:varchar(2)');
        $this->hasColumn('title', 'N:varchar(255)');
        $this->hasColumn('body', 'N:text');
        $this->hasColumn('completed', 'U:tinyint(4)|0');
    }
}
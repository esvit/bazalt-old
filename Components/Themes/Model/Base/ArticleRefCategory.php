<?php

namespace Components\News\Model\Base;

abstract class ArticleRefCategory extends \Framework\CMS\ORM\Record
{
    const TABLE_NAME = 'com_news_ref_categories';

    const MODEL_NAME = 'Components\News\Model\ArticleRefCategory';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('news_id', 'PU:int(10)');
        $this->hasColumn('category_id', 'PU:int(10)');
    }

}
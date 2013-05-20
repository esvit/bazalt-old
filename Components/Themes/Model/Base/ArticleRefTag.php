<?php

namespace Components\News\Model\Base;

abstract class ArticleRefTag extends \Framework\CMS\ORM\Record
{
    const TABLE_NAME = 'com_news_ref_tags';

    const MODEL_NAME = 'Components\News\Model\ArticleRefTag';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('news_id', 'PU:int(10)');
        $this->hasColumn('tag_id', 'PU:int(10)');
    }
}
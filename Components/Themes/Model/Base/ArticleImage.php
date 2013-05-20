<?php

namespace Components\News\Model\Base;

abstract class ArticleImage extends \Framework\CMS\ORM\Record
{
    const TABLE_NAME = 'com_news_images';

    const MODEL_NAME = 'Components\News\Model\ArticleImage';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PUA:int(10)');
        $this->hasColumn('news_id', 'U:int(10)');
        $this->hasColumn('image', 'N:varchar(255)');
        $this->hasColumn('order', 'U:int(10)');
    }
}
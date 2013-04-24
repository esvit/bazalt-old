<?php

namespace Components\Pages\Model\Base;

abstract class PageImage extends \Framework\CMS\Model\Base\Record
{
    const TABLE_NAME = 'com_pages_images';

    const MODEL_NAME = 'Components\Pages\Model\PageImage';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PUA:int(10)');
        $this->hasColumn('page_id', 'U:int(10)');
        $this->hasColumn('image', 'N:varchar(255)');
        $this->hasColumn('order', 'U:int(10)');
    }

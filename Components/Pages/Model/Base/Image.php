<?php

namespace Components\Pages\Model\Base;

abstract class Image extends \Framework\CMS\ORM\Record
{
    const TABLE_NAME = 'com_pages_images';

    const MODEL_NAME = 'Components\Pages\Model\Image';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PUA:int(10)');
        $this->hasColumn('page_id', 'U:int(10)');
        $this->hasColumn('url', 'N:varchar(255)');
        $this->hasColumn('sort_order', 'U:int(10)');
    }
}
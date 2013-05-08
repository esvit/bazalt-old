<?php

namespace Components\Gallery\Model\Base;

abstract class AlbumLocale extends \Framework\CMS\ORM\Record
{
    const TABLE_NAME = 'com_gallery_albums_locale';

    const MODEL_NAME = 'Components\Gallery\Model\AlbumLocale';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PU:int(10)');
        $this->hasColumn('lang_id', 'PU:int(10)');
        $this->hasColumn('title', 'varchar(255)');
        $this->hasColumn('description', 'mediumtext');
    }
}

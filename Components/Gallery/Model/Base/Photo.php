<?php

namespace Components\Gallery\Model\Base;

abstract class Photo extends \Framework\CMS\ORM\Record
{
    const TABLE_NAME = 'com_gallery_photo';

    const MODEL_NAME = 'Components\Gallery\Model\Photo';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PUA:int(10)');
        $this->hasColumn('album_id', 'U:int(10)');
        $this->hasColumn('site_id', 'UN:int(10)');
        $this->hasColumn('title', 'varchar(255)');
        $this->hasColumn('image', 'varchar(255)');
        $this->hasColumn('width', 'U:int(10)');
        $this->hasColumn('height', 'U:int(10)');
        $this->hasColumn('description', 'text');
        $this->hasColumn('thumbs', 'text');
        $this->hasColumn('order', 'U:int(10)');
    }

    public function initRelations()
    {
        $this->hasRelation('Album', new \Bazalt\ORM\Relation\One2One('Components\Gallery\Model\Album', 'album_id', 'id'));
    }

    public function initPlugins()
    {
        $this->hasPlugin('Framework\CMS\ORM\Localizable', ['title', 'description']);
    }
}
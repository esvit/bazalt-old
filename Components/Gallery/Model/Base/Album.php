<?php

namespace Components\Gallery\Model\Base;

use Framework\CMS as CMS;

abstract class Album extends \Framework\CMS\ORM\Record
{
    const TABLE_NAME = 'com_gallery_albums';

    const MODEL_NAME = 'Components\Gallery\Model\Album';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PUA:int(10)');
        $this->hasColumn('site_id', 'UN:int(10)');
        $this->hasColumn('alias', 'varchar(255)');
        $this->hasColumn('images_count', 'N:int(1)');
        $this->hasColumn('is_hidden', 'N:int(1)');
        $this->hasColumn('is_publish', 'N:int(1)');
    }

    public function initRelations()
    {
        $this->hasRelation('Photos', new \ORM_Relation_One2Many('Components\Gallery\Model\Photo', 'id', 'album_id'));
        //$this->hasRelation('Elements', new \CMS_ORM_Relation_LocalizableNestedSet('ComGallery_Model_Album', 'site_id'));
        //$this->hasRelation('PublicElements', new \CMS_ORM_Relation_LocalizableNestedSet('ComGallery_Model_Album', 'site_id', null, array('is_publish' => 1)));
    }

    public function initPlugins()
    {
        $this->hasPlugin('Framework\CMS\ORM\Localizable', ['title', 'description']);
    }
}
<?php
/**
 * Data model
 *
 * @category  DataModel
 * @package   DataModel
 * @author    DataModel Generator v1.1
 * @version   Revision
 */
/**
 * Data model for table "cms_components"
 *
 * @category  DataModel
 * @package   DataModel
 * @author    DataModel Generator v1.1
 * @version   Revision
 *
 * @property-read DbInt Id
 * @property-read DbString Name
 */
namespace Components\Pages\Model\Base;

abstract class Page extends \Framework\CMS\ORM\Record
{
    const TABLE_NAME = 'com_pages_pages';
    
    const MODEL_NAME = 'Components\Pages\Model\Page';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PA:int(10)');
        $this->hasColumn('site_id', 'U:int(10)');
        $this->hasColumn('user_id', 'N:int(10)');
        $this->hasColumn('category_id', 'UN:int(10)');
        $this->hasColumn('title', 'N:varchar(255)');
        $this->hasColumn('url', 'N:varchar(255)');
        $this->hasColumn('body', 'N:longtext');
        $this->hasColumn('is_published', 'U:tinyint(1)|0');
    }

    public function initRelations()
    {
        $this->hasRelation('Category', new \Bazalt\ORM\Relation\One2One('Components\Pages\Model\Category', 'category_id', 'id'));
        $this->hasRelation('Images', new \Bazalt\ORM\Relation\One2Many('Components\Pages\Model\Image', 'id', 'page_id'));
    }

    public function initPlugins()
    {
        $this->hasPlugin('Framework\CMS\ORM\Localizable', ['title', 'body']);

        $this->hasPlugin('Bazalt\ORM\Plugin\Timestampable', ['created' => 'created_at', 'updated' => 'updated_at']);
    }
}

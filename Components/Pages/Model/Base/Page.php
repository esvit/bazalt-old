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
        $this->hasColumn('category_id', 'UN:int(10)');
        $this->hasColumn('title', 'N:varchar(255)');
        $this->hasColumn('body', 'N:longtext');
        $this->hasColumn('user_id', 'N:int(10)');
        $this->hasColumn('publish', 'U:tinyint(1)|0');
        $this->hasColumn('url', 'N:varchar(255)');
        $this->hasColumn('template', 'N:varchar(255)');
        $this->hasColumn('order', 'U:tinyint(3)|0');
    }

    public function initRelations()
    {
        $this->hasRelation('Category', new \ORM_Relation_One2One('Components\Pages\Model\Category', 'category_id', 'id'));
        $this->hasRelation('Categories', new \ORM_Relation_Many2Many('\Framework\CMS\Model\Category', 'page_id', 'Components\Pages\Model\PageRefCategory', 'category_id'));
        $this->hasRelation('Images', new \ORM_Relation_One2Many('Components\Pages\Model\PageImage', 'id', 'page_id'));
    }

    public function initPlugins()
    {
        $this->hasPlugin('Framework\CMS\ORM\Localizable', ['title', 'body']);

        $this->hasPlugin('Framework\System\ORM\Plugin\Timestampable', ['created' => 'created_at', 'updated' => 'updated_at']);
    }
}

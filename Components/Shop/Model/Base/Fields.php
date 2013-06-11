<?php
/**
 * Data model
 *
 * @category  DataModels
 * @package   DataModel
 * @author    Bazalt CMS (http://bazalt-cms.com/)
 * @version   SVN: $Id$
 */
/**
 * Data model for table "com_ecommerce_fields"
 *
 * @category  DataModels
 * @package   DataModel
 * @author    Bazalt CMS (http://bazalt-cms.com/)
 * @version   Release: $Revision$
 *
 * @property-read int $id
 * @property-read varchar $name
 * @property-read int $type
 * @property-read tinyint $require
 */
abstract class ComEcommerce_Model_Base_Fields extends CMS_Model_Base_Record
{
    const TABLE_NAME = 'com_ecommerce_fields';

    const MODEL_NAME = 'ComEcommerce_Model_Field';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PUA:int(10)');
        $this->hasColumn('data', 'longtext');
        $this->hasColumn('title', 'N:varchar(255)');
        $this->hasColumn('type', 'U:int(10)');
        $this->hasColumn('require', 'U:tinyint(1)|0');
        $this->hasColumn('is_published', 'U:tinyint(1)|0');
        $this->hasColumn('is_filter', 'U:tinyint(1)|0');
        $this->hasColumn('order', 'U:int(10)|0');
    }

    public function initRelations()
    {
    }

    public function initPlugins()
    {
        $this->hasPlugin('CMS_ORM_Localizable', array(
            'fields' => array('title'),
            'type' => CMS_ORM_Localizable::ROWS_LOCALIZABLE
        ));
        $this->hasPlugin('ORM_Plugin_Serializable', 'data');
    }
    
    public function getById($id)
    {
        return parent::getRecordById($id, self::MODEL_NAME);
    }

    public function getAll($limit = null)
    {
        return parent::getAllRecords($limit, self::MODEL_NAME);
    }

    public function select($fields = null)
    {
        return ORM::select(self::MODEL_NAME, $fields);
    }

    public function insert($fields = null)
    {
        return ORM::insert(self::MODEL_NAME, $fields);
    }

}
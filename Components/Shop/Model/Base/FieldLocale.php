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
 * Data model for table "com_ecommerce_fields_locale"
 *
 * @category  DataModels
 * @package   DataModel
 * @author    Bazalt CMS (http://bazalt-cms.com/)
 * @version   Release: $Revision$
 *
 * @property-read int $id
 * @property-read int $lang_id
 * @property-read varchar $title
 * @property-read tinyint $completed
 */
abstract class ComEcommerce_Model_Base_FieldLocale extends CMS_Model_Base_Record
{
    const TABLE_NAME = 'com_ecommerce_fields_locale';

    const MODEL_NAME = 'ComEcommerce_Model_FieldLocale';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PU:int(10)|0');
        $this->hasColumn('lang_id', 'PU:int(10)');
        $this->hasColumn('title', 'N:varchar(255)');
        $this->hasColumn('completed', 'U:tinyint(1)|0');
    }

    public function initRelations()
    {
    }
    
    public function initPlugins()
    {
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
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
 * Data model for table "com_ecommerce_product_types"
 *
 * @category  DataModels
 * @package   DataModel
 * @author    Bazalt CMS (http://bazalt-cms.com/)
 * @version   Release: $Revision$
 *
 * @property-read int $id
 * @property-read varchar $tmp
 */
abstract class ComEcommerce_Model_Base_ProductTypes extends CMS_Model_Base_Record
{
    const TABLE_NAME = 'com_ecommerce_product_types';

    const MODEL_NAME = 'ComEcommerce_Model_ProductTypes';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PUA:int(10)');
        $this->hasColumn('category_id', 'U:int(10)');
        $this->hasColumn('title', 'N:varchar(255)');
    }

    public function initRelations()
    {
        $this->hasRelation('Fields', new ORM_Relation_Many2Many('ComEcommerce_Model_Field', 'product_type_id', 'ComEcommerce_Model_ProductTypesFields', 'field_id'));
        $this->hasRelation('Products', new ORM_Relation_One2Many('ComEcommerce_Model_Product', 'id', 'type_id'));
        $this->hasRelation('Elements', new ORM_Relation_NestedSet('ComEcommerce_Model_ProductTypes', 'category_id'));
    }
    
    public function initPlugins()
    {
        $this->hasPlugin('CMS_ORM_Localizable', array(
            'fields' => array('title'),
            'type' => CMS_ORM_Localizable::ROWS_LOCALIZABLE
        ));
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
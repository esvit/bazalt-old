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
 * Data model for table "com_ecommerce_products_categories"
 *
 * @category  DataModels
 * @package   DataModel
 * @author    Bazalt CMS (http://bazalt-cms.com/)
 * @version   Release: $Revision$
 *
 * @property-read int $product_id
 * @property-read int $field_id
 * @property-read varchar $value
 */
abstract class ComEcommerce_Model_Base_ProductsCategories extends CMS_Model_Base_Record
{
    const TABLE_NAME = 'com_ecommerce_products_categories';

    const MODEL_NAME = 'ComEcommerce_Model_ProductsCategories';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('product_id', 'PU:int(10)');
        $this->hasColumn('category_id', 'PU:int(10)');
    }

    public function initRelations()
    {
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
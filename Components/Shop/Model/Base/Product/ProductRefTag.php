<?php

abstract class ComEcommerce_Model_Base_ProductRefTag extends CMS_Model_Base_Record
{
    const TABLE_NAME = 'com_ecommerce_products_ref_tags';

    const MODEL_NAME = 'ComEcommerce_Model_ProductRefTag';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('product_id', 'PU:int(10)');
        $this->hasColumn('tag_id', 'PU:int(10)');
    }

    public function initRelations()
    {
    }

    public static function getById($id)
    {
        return parent::getRecordById($id, self::MODEL_NAME);
    }

    public static function getAll($limit = null)
    {
        return parent::getAllRecords($limit, self::MODEL_NAME);
    }

    public static function select($fields = null)
    {
        return ORM::select(self::MODEL_NAME, $fields);
    }

    public static function insert($fields = null)
    {
        return ORM::insert(self::MODEL_NAME, $fields);
    }
}
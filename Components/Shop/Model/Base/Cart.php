<?php

abstract class ComEcommerce_Model_Base_Cart extends CMS_Model_Base_Record
{
    const TABLE_NAME = 'com_ecommerce_cart';

    const MODEL_NAME = 'ComEcommerce_Model_Cart';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PUA:int(10)');
        $this->hasColumn('site_id', 'UN:int(10)');
        $this->hasColumn('user_id', 'U:int(10)');
        $this->hasColumn('session_id', 'varchar(50)');
        $this->hasColumn('price', 'varchar(50)');
    }

    public function initRelations()
    {
        $this->hasRelation('Products', new ORM_Relation_Many2Many('ComEcommerce_Model_Product', 'cart_id', 'ComEcommerce_Model_CartRefProduct', 'product_id'));
        $this->hasRelation('User', new ORM_Relation_One2One('CMS_Model_User', 'user_id', 'id'));
    }

    public function initPlugins()
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
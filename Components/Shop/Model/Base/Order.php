<?php

abstract class ComEcommerce_Model_Base_Order extends CMS_Model_Base_Record
{
    const TABLE_NAME = 'com_ecommerce_orders';

    const MODEL_NAME = 'ComEcommerce_Model_Order';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PUA:int(10)');
        $this->hasColumn('site_id', 'U:int(10)');
        $this->hasColumn('cart_id', 'U:int(10)');
        $this->hasColumn('price', 'double(10,4)');
        $this->hasColumn('name', 'varchar(50)');
        $this->hasColumn('phone', 'varchar(50)');
        $this->hasColumn('address', 'text');
        $this->hasColumn('comment', 'text');
        $this->hasColumn('status', 'U:int(10)');
    }

    public function initRelations()
    {
        $this->hasRelation('Products', new ORM_Relation_Many2Many('ComEcommerce_Model_Product', 'order_id', 'ComEcommerce_Model_OrderRefProduct', 'product_id'));
        $this->hasRelation('Cart', new ORM_Relation_One2One('ComEcommerce_Model_Cart', 'cart_id', 'id'));
    }
    
    public function initPlugins()
    {
        $this->hasPlugin('ORM_Plugin_Timestampable', array(
            'created' => 'created_at',
            'updated' => 'updated_at'
        ));
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
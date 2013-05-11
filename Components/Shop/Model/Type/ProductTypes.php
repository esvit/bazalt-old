<?php
/**
 * Data model for table com_ecommerce_product_types
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
 */
class ComEcommerce_Model_ProductTypes extends ComEcommerce_Model_Base_ProductTypes
{
    public static function create()
    {
        $o = new ComEcommerce_Model_ProductTypes();
        return $o;
    }

    public static function getCollection()
    {
        $q = ORM::select('ComEcommerce_Model_ProductTypes f');
        return new CMS_ORM_Collection($q);
    }

    public function getFields($onlyPublished = null, $forFilter = null, $withoutNested = false)
    {
        $qIn = ORM::select('ComEcommerce_Model_ProductTypes t', 't.id')
                  ->where('t.lft < ? AND t.rgt > ?', array($this->lft, $this->rgt) );

        $q = ORM::select('ComEcommerce_Model_Field f')
                ->innerJoin('ComEcommerce_Model_ProductTypesFields tf', array('field_id', 'f.id'));

        if ($withoutNested) {
            $q->andWhere('tf.product_type_id = ?', $this->id);
        } else {
            $q->andWhereGroup()
              ->andWhere('tf.product_type_id = ?', $this->id)
              ->orWhereIn('tf.product_type_id', $qIn)
              ->endWhereGroup();
        }

        if ($onlyPublished != null) {
            $q->andWhere('f.is_published = ?', 1);
        }
        if ($forFilter != null) {
            $q->andWhere('f.is_filter = ?', 1);
        }
        $q->orderBy('f.order, f.id');
        $fields = $q->fetchAll();

        return $fields;
    }
    
    public static function getList()
    {
        $q = ComEcommerce_Model_ProductTypes::select()
                ->where('depth > 0')
                ->orderBy('lft ASC');

        return $q->fetchAll();
    }

    public static function getRoot()
    {
        $q = ORM::select('ComEcommerce_Model_ProductTypes')
                ->where('depth = ?', 0);

        $root = $q->fetch();
        if (!$root) {
            $root = new ComEcommerce_Model_ProductTypes();
            $root->category_id = 1;
            $root->lft = 1;
            $root->rgt = 2;
            $root->depth = 0;
            $root->save();
        }
        return $root;
    }
}
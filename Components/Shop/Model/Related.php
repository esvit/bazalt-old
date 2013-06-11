<?php
/**
 * Data model for table com_ecommerce_related
 *
 * @category  DataModels
 * @package   DataModel
 * @author    Bazalt CMS (http://bazalt-cms.com/)
 * @version   SVN: $Id$
 */

/**
 * Data model for table "com_ecommerce_related"
 *
 * @category  DataModels
 * @package   DataModel
 * @author    Bazalt CMS (http://bazalt-cms.com/)
 * @version   Release: $Revision$
 */
class ComEcommerce_Model_Related extends ComEcommerce_Model_Base_Related
{
    public static function deleteItem($id, $pid){
        $builder = ORM::delete('ComEcommerce_Model_Related');

        $builder->where('product_id = ?', $id);
        $builder->andWhere('related_id = ?', $pid);

        $builder->exec();
        return $builder->rowCount();
    }

    public static function insert($id, $pid){
        $fields = array(
            'product_id' => $id,
            'related_id' => $pid,
        );
        return ORM::insert(self::MODEL_NAME, $fields);
    }
}
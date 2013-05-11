<?php
/**
 * Data model for table com_ecommerce_products_prices
 *
 * @category  DataModels
 * @package   DataModel
 * @author    Bazalt CMS (http://bazalt-cms.com/)
 * @version   SVN: $Id$
 */

/**
 * Data model for table "com_ecommerce_products_prices"
 *
 * @category  DataModels
 * @package   DataModel
 * @author    Bazalt CMS (http://bazalt-cms.com/)
 * @version   Release: $Revision$
 */
class ComEcommerce_Model_ProductsPrices extends ComEcommerce_Model_Base_ProductsPrices
{
    public static function getByParams($params)
    {
        $q = ComEcommerce_Model_ProductsPrices::select()
            ->where('product_id = ?', $params['product_id'])
            ->andWhere('account_id = ?', $params['account_id'])
            ->limit(4);
        return $q->fetch();
    }
}

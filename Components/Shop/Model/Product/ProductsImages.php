<?php
/**
 * Data model for table com_ecommerce_products_images
 *
 * @category  DataModels
 * @package   DataModel
 * @author    Bazalt CMS (http://bazalt-cms.com/)
 * @version   SVN: $Id$
 */

/**
 * Data model for table "com_ecommerce_products_images"
 *
 * @category  DataModels
 * @package   DataModel
 * @author    Bazalt CMS (http://bazalt-cms.com/)
 * @version   Release: $Revision$
 */
class ComEcommerce_Model_ProductsImages extends ComEcommerce_Model_Base_ProductsImages
{
    const MAX_WIDTH = 1024;

    const MAX_HEIGHT = 768;

    public static function create()
    {
        $photo = new ComEcommerce_Model_ProductsImages();
        return $photo;
    }
    

    public static function getMaxOrder($product)
    {
        $q = ORM::select('ComEcommerce_Model_ProductsImages i', 'MAX(i.order) AS `max`')
                ->andWhere('i.product_id = ?', $product->id);

        $res = $q->fetch('stdClass');
        return ($res == null) ? 0 : $res->max;
    }

    public function getThumb($size = 'big')
    {
        if ($size == 'real' || $size == 'original') {
            return $this->image;
        }
        return CMS_Image::getThumb($this->image, $size);
    }

    public function getUrl()
    {
        return relativePath(UPLOAD_DIR . $this->image);
    }
    
    public function toArray($size = 'big')
    {
        $res = parent::toArray();
        $res['url'] = $this->getUrl();
        $res['thumb'] = $this->getThumb($size);
        return $res;
    }
    
    public static function updateOrder($id, $order)
    {
        $q = ORM::update('ComEcommerce_Model_ProductsImages')
                            ->set('order', $order)
                            ->where('id = ?', $id);

        $q->exec();
    }
}

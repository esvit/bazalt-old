<?php

namespace Components\Shop\Model;

class ProductImage extends Base\ProductImage
{
    public static function create()
    {
        $photo = new ProductImage();
        return $photo;
    }

    public static function getMaxOrder($product)
    {
        $q = ORM::select('Components\Shop\Model\ProductImage i', 'MAX(i.order) AS `max`')
                ->andWhere('i.product_id = ?', $product->id);

        $res = $q->fetch('stdClass');
        return ($res == null) ? 0 : $res->max;
    }

    public function getUrl()
    {
        return relativePath(UPLOAD_DIR . $this->image);
    }

    public static function updateOrder($id, $order)
    {
        $q = ORM::update('Components\Shop\Model\ProductImage')
                            ->set('order', $order)
                            ->where('id = ?', $id);
        $q->exec();
    }
}
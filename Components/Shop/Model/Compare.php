<?php

class ComEcommerce_Model_Compare extends ComEcommerce_Model_Base_Compare
{
    public static function create(ComEcommerce_Model_Cart $cart, ComEcommerce_Model_Product $product)
    {
        $comp = new ComEcommerce_Model_Compare();
        $comp->cart_id = $cart->id;
        $comp->product_id = $product->id;
        $comp->type_id = $product->type_id;
        return $comp;
    }

    public static function getByCartAndProduct(ComEcommerce_Model_Cart $cart, ComEcommerce_Model_Product $product)
    {
        $q = ORM::select('ComEcommerce_Model_Compare c')
                ->where('c.cart_id = ?', $cart->id)
                ->andWhere('c.product_id = ?', $product->id);

        return $q->fetch();
    }

    public static function getByCartAndTypeId(ComEcommerce_Model_Cart $cart, $typeId)
    {
        $q = ORM::select('ComEcommerce_Model_Compare c')
                ->where('c.cart_id = ?', $cart->id)
                ->andWhere('c.type_id = ?', $typeId);

        return $q->fetchAll();
    }

    public static function getProductsByCartAndTypeId(ComEcommerce_Model_Cart $cart, $typeId)
    {
        $q = ORM::select('ComEcommerce_Model_Product p')
                ->innerJoin('ComEcommerce_Model_Compare c', array('product_id', 'p.id'))
                ->where('c.cart_id = ?', $cart->id)
                ->andWhere('c.type_id = ?', $typeId);

        return $q->fetchAll();
    }
}
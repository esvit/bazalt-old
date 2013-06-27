<?php

namespace Components\Shop\Webservice;

use Framework\CMS\Webservice\Response,
    Framework\System\Session\Session,
    Framework\System\Data as Data,
    Framework\CMS as CMS;
use Components\Shop\Model\ProductImage;
use Framework\Core\Helper\Url;
use Components\Shop\Model\Product,
    Components\Shop\Model\Category,
    Components\Shop\Model\WishList;

/**
 * @uri /wish-lists
 */
class WishLists extends CMS\Webservice\Rest
{
    /**
     * @method POST
     * @provides application/json
     * @json
     * @return \Tonic\Response
     */
    public function addProduct()
    {
        $data = new Data\Validator((array)$this->request->data);
        $product = isset($data['product_id']) ? Product::getById((int)$data['product_id']) : null;
        if(!$product) {
            return new Response(404, sprintf('Product with id "%s" not found', $data['id']));
        }

        //@todo check is guest
        $user = CMS\Model\User::getById(1);
//        $user = CMS\User::get();
//        if($user->isGuest()) {
//            return new Response(403, 'Access denied');
//        }

//        print_r($product);
        WishList::saveProduct($product, $user);
        $count = WishList::getCountForUser($user);

        return new Response(200, ['count' => $count]);
    }

    /**
     * @method DELETE
     * @provides application/json
     * @json
     * @return \Tonic\Response
     */
    public function deleteProduct()
    {
        $product = isset($_GET['product_id']) ? Product::getById((int)$_GET['product_id']) : null;
        if(!$product) {
            return new Response(404, sprintf('Product with id "%s" not found', $_GET['id']));
        }

        //@todo check is guest
        $user = CMS\Model\User::getById(1);
//        $user = CMS\User::get();
//        if($user->isGuest()) {
//            return new Response(403, 'Access denied');
//        }

//        print_r($product);
        WishList::deleteProduct($product, $user);
        $count = WishList::getCountForUser($user);

        return new Response(200, ['count' => $count]);
    }

    /**
     * @method GET
     * @provides application/json
     * @json
     * @return \Tonic\Response
     */
    public function get()
    {
        /*$user = CMS\User::get();
        if ($user->isGuest()) {
            return new Response(200, null);
        }*/
        //@todo check is guest
        $user = CMS\Model\User::getById(1);
        $collection = WishList::getProductsCollection($user);

        return new Response(200, $collection);
    }

}
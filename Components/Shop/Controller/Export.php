<?php

class ComEcommerce_Controller_Export extends CMS_Component_Controller
{
    protected static function array2Xml($student_info, &$xml_student_info)
    {
        foreach($student_info as $key => $value) {
            if(is_array($value)) {
                if(!is_numeric($key)){
                    $subnode = $xml_student_info->addChild("$key");
                    self::array2Xml($value, $subnode);
                }
                else{
                    $subnode = $xml_student_info->addChild("key_$key");
                    self::array2Xml($value, $subnode);
                }
            }
            else {
                if(!is_numeric($key)){
                    $xml_student_info->addChild("$key","$value");
                }else{
                    $xml_student_info->addChild("key_$key","$value");
                }
            }
        }
    }

    public function ordersAction($userId, $userSecret)
    {
        $user = CMS_Model_User::getById((int)$userId);
        if (!$user) {
            throw new Exception('User not found');
        }
        $code = $user->setting('CMS.UserSecretCode');
        if ($userSecret != $code) {
            throw new Exception('Invalid user secret code');
        }

        $result = array();
        $orders = ComEcommerce_Model_Order::getNewOrders();
        foreach ($orders as $order) {
            $item = $order->toArray();
            unset($item['site_id']);
            unset($item['cart_id']);

            $products = $order->Products->get();

            $item['products'] = array();
            foreach ($products as $product) {
                $pr = array(
                    'code' => $product->code,
                    'price' => $product->price,
                    'count' => $product->count
                );

                $item['products'][] = $pr;
            }
            $result []= $item;
        }

        $xml = new SimpleXMLElement("<?xml version=\"1.0\"?><orders></orders>");
        self::array2Xml($result,$xml);

        header("Content-Type: text/xml");
        print $xml->asXML();
        exit;
    }
}
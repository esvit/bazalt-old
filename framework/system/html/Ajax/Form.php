<?php

class Html_Ajax_Form extends Html_Element_Form
{
    public static function isAjax()
    {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest');
    }

    public function begin($attributes = array())
    {
        if (self::isAjax() && $_REQUEST['form'] == $this->id()) {
            $method = $_REQUEST['method'];
            $element = $_REQUEST['element'];
            $params = $_REQUEST['params'];

            $method = 'ajax' . $method;
            ob_end_clean();
            $this->initElement();
            if (!empty($element)) {
                $findElement = $this->findElementByID($element);
                if (!$findElement) {
                    throw new Exception('Element not found ' . $element);
                }
                $result = $findElement->__ajaxCall($method, $params);
            } else {
                $result = $this->__ajaxCall($method, $params);
            }

            header('Content-type: application/json; charset=UTF-8');
            CMS_Browser::headerNoCache();
            MetaTags::Singleton()->noIndex()->noFollow();
            echo json_encode($result);
            exit;
        }
        return parent::begin();
    }
}
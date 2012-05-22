<?php

class Site_Service_Paging extends CMS_Application_Service
{
    protected static $currentPage = 1;

    public static function getPage()
    {
        return self::$currentPage;
    }

    public function prepareUrl(&$url)
    {
        $pageCurrent = 0;
        if (preg_match('/\/page\/(?P<digit>\d+)/', $url, $matches)) {
            if (!empty($matches['digit'])) {
                $url = rtrim($url, '/');
                $pageCurrent = intval($matches['digit']);
                $url = substr($url, 0, -(5 + strlen($matches['digit'])));
            }
        }
        if (array_key_exists('page', $_GET)) {
            $pageCurrent = intval($_GET['page']);
        }
        $pageCurrent = ($pageCurrent <= 0) ? 1 : $pageCurrent;
        
        self::$currentPage = $pageCurrent;
        $this->view->assignGlobal('pageCurrent', $pageCurrent);
    }
}
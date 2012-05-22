<?php

class CMS_View_PhpEngine extends View_PhpEngine
{
    public function __construct()
    {
        self::includeHelpers();
    }

    public static function includeHelpers()
    {
        $helpers = glob(dirname(__FILE__) . '/PHP/helpers/*.php');
        foreach ($helpers as $helper) {
            require_once $helper;
        }
    }
}
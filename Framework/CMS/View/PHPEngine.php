<?php

namespace Framework\CMS\View;

class PHPEngine extends \Bazalt\View\PHP\Engine
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
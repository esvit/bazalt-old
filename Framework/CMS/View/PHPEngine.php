<?php

namespace Framework\CMS\View;

use Framework\System\View\PHP\Engine;

class PHPEngine extends Engine
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
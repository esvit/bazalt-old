<?php

namespace Framework\CMS;

class Route extends \Framework\System\Routing\Route
{
    protected function __construct($name, $baseRule, Route $baseRoute = null)
    {
        parent::__construct($name, $baseRule, $baseRoute);

        $this->where('_meta', function($url, $name, $param, $params, $route) {
            if (!$param) {
                $route->param('_meta', new MetaInfo($route));
            }
            return true;
        });
    }

    public function getMetaInfo()
    {
        return $this->param('_meta');
    }

    public function noIndex()
    {
    }
}
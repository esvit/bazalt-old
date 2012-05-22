<?php

using('Framework.System.Routing');

class CMS_Mapper extends Mapper
{
    protected static $routeObject = null;

    protected static $nestedSetType = false;

    public function __construct($baseUrl = null, $options = array())
    {
        self::$nestedSetType = CMS_Option::get('CmsRoutes.NestedSetWithPath', true);

        parent::__construct($baseUrl, $options);
    }

    public static function setRouteObject(ORM_Record $object)
    {
        if (!($object instanceof ORM_Record)) {
            throw new Exception('Invalid route object, cant be only ORM_Record');
        }
        self::$routeObject = $object;
    }

    public static function getRouteObject()
    {
        return self::$routeObject;
    }

    public static function getDispatchedComponent()
    {
        return self::$dispatchedRoute->Component;
    }

    public function connect($urlRule, $defaults = array())
    {
        $this->getLogger()->info(sprintf('Connect route "%s" "%s"', $this->baseUrl, $urlRule));
        return $this->connectRule(new CMS_Routing_Rule($this, $urlRule, $this->baseUrl, array_merge($this->defaults, $defaults)));
    }

    public function connectSubmapper(Mapper $map, $subPattern, $defaults = array())
    {
        $this->getLogger()->info(sprintf('Connect submapper "%s" "%s"', $this->baseUrl, $subPattern));

        $this->submappers[$subPattern] = $map;
        $rule = new CMS_Routing_SubmapperRule($map, $subPattern, $this->baseUrl, $defaults);

        return $this->connectRule($rule);
    }

    protected function getCacheKey($key)
    {
        return CMS_Application::current()->name() . parent::getCacheKey($key);
    }

    public function generateUrlPattern($name, $withHostname = false)
    {
        $url = parent::generateUrlPattern($name, $withHostname);

        $route = self::$namedRoutes[$name];
        if (!$route) {
            throw new Exception('Route "' . $name . '" not found');
        }
        if (!$route->isLocalizable()) {
            $urlPrefix = CMS_Language::getLanguagePrefix();

            if (!empty($urlPrefix)) {
                return substr($url, strlen($urlPrefix));
            }
        }
        return $url;
    }

    protected function generateUrl($name, $params = array(), $withHostname = false)
    {
        $newParams = array();

        foreach ($params as $key => $param) {
            if ($param instanceof ORM_Relation_NestedSet) {
                $cacheKey = __FUNCTION__ . $param->BaseObject->id;
                $url = Cache::Singleton()->getCache($cacheKey);

                if (!$url) {
                    if (self::$nestedSetType) {
                        foreach ($param->getPath() as $elem) {
                            if ($elem->depth > 0) {
                                $url .= $elem->alias . '/';
                            }
                        }
                    }
                    $url .= $param->BaseObject->alias;
                    Cache::Singleton()->setCache($cacheKey, $url);
                }

                $newParams[$key] = $url;
            } else {
                $newParams[$key] = $param;
            }
        }
        $url = parent::generateUrl($name, $newParams, $withHostname);
        if (isset($params['page']) && (int)$params['page'] > 1) {
            $pUrl = new Datatype_Url($url);
            $pUrl->path(rtrim($pUrl->path(), '/') . '/page/' . (int)$params['page']);
            $url = $pUrl->toString();
        }
        return $url;
    }

    public function dispatch($url)
    {
        $route = $this->find($url);
        if (!$route) {
            throw new Routing_Exception_NoMatch('Route not found');
        }

        self::$dispatchedRoute = $route;

        if ($route->rule()->isAuthorizationRequest() && !CMS_User::isLogined()) {
            Session::Singleton()->backUrl = Datatype_Url::getRequestUrl();
            if($route->Rule->getAuthorizationUrl() == null) {
                Url::redirect(CMS_Mapper::urlFor('CMS.Login'));
            } else {
                Url::redirect($route->Rule->getAuthorizationUrl());
            }
        }
        
        if (!$route->rule()->isAllowed()) {
            throw new CMS_Exception_AccessDenied();
        }

        $route->dispatch($this->folder);
    }
}
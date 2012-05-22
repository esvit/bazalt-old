<?php

class CMS_Routing_Rule extends Routing_Rule
{
    protected $isLocalizable = true;

    protected $isAuthorizationRequest = false;
    
    protected $authorizationUrl = null;
    
    protected $deny = array();
    
    protected $allow = array();
    
    protected $aclUsed = false;

    public function authorizationRequest($url = null)
    {
        $this->authorizationUrl = $url;
        $this->isAuthorizationRequest = true;
        return $this;
    }

    public function getAuthorizationUrl()
    {
        return $this->authorizationUrl;
    }
    
    public function isAuthorizationRequest()
    {
        return $this->isAuthorizationRequest;
    }

    public function notLocalizable()
    {
        $this->isLocalizable = false;
        return $this;
    }

    public function isLocalizable()
    {
        return $this->isLocalizable;
    }

    public function noIndex()
    {
        $this->addPreAction(array($this, 'addNoIndexTag'));
        return $this;
    }

    public function addNoIndexTag()
    {
        Metatags::Singleton()->noIndex();
    }

    public function noFollow()
    {
        $this->addPreAction(array($this, 'addNoFollowTag'));
        return $this;
    }
    
    public function denyAll()
    {
        $this->deny = array('*');
        $this->allow = array();
        return $this;
    }
    
    public function deny($componentName, $aclAction)
    {
        $this->deny[$aclAction] = $componentName;
        return $this;
    }
    
    public function allowAll()
    {
        $this->allow = array('*');
        return $this;
    }
    
    public function allow($componentName, $aclAction)
    {
        $this->denyAll();
        $this->allow[$aclAction] = $componentName;
        return $this;
    }
    
    public function isAllowed()
    {
        if(count($this->allow) == 0 && count($this->deny) == 0) {
            return true;
        }
        
        $allowAll = false;

        $allowed = true;
        $denied = false;
        
        $allow = $this->allow;
        $deny = $this->deny;

        if(count($allow) > 0 && $allow[0] == '*') {
            unset($allow[0]);
            $allowAll = true;
        }
        if(count($deny) > 0 && $deny[0] == '*') {
            unset($deny[0]);
        }
        foreach($allow as $aclAction => $componentName) {
            $allowed &= CMS_User::getUser()->hasRight($componentName, $aclAction);
        }
        foreach($deny as $aclAction => $componentName) {
            $denied |= CMS_User::getUser()->hasRight($componentName, $aclAction);
        }
        if(($allowAll && $denied) || !$allowed) {
            return false;
        }
        return true;
    }

    public function addNoFollowTag()
    {
        Metatags::Singleton()->noFollow();
    }

    public function noArchive()
    {
        $this->addPreAction(array($this, 'addNoArchiveTag'));
        return $this;
    }

    public function addNoArchiveTag()
    {
        Metatags::Singleton()->noArchive();
    }

    public function noSnippet()
    {
        $this->addPreAction(array($this, 'addNoSnippetTag'));
        return $this;
    }

    public function addNoSnippetTag()
    {
        Metatags::Singleton()->noSnippet();
    }

    public function component($component)
    {
        $this->defaults['component'] = $component;
        return $this;
    }

    protected function createRoute($url, $params)
    {
        return new CMS_Route($url, $this, $params);
    }

    protected function checkFunctionCondition($url, &$params, $route, $options)
    {
        return $this->checkFunctionConditionForRoute($url, $params, $route, $options);
    }

    public function checkFunctionConditionForRoute($url, &$params, $route, $options)
    {
        $component = null;
        if (!array_key_exists('component', $options)) {
            return parent::checkFunctionCondition($url, $params, $route, $options);
        }

        $func = $options['function'];
        $component = CMS_Bazalt::getComponent($options['component']);
        unset($options['component']);
        unset($options['function']);

        $callParams = array($url, $route, &$params, $options);
        return call_user_func_array(array($component, $func), $callParams);
    }
}
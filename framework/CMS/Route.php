<?php

class CMS_Route extends Routing_Route
{
    protected $component = null;

    protected function getRouteController()
    {
        // deny search bots
        if (!CMS_Option::get(CMS_Bazalt::ALLOWSEARCHBOT_OPTION, true)) {
            $this->rule->noIndex()
                       ->noFollow();
        }

        if (empty($this->rule->Defaults['component']) && empty($this->params['component'])) {
            $app = CMS_Application::current();
            $controllerName = $app->name() . '_Controller_' . $this->controller;
            if (!class_exists($controllerName)) {
                return parent::getRouteController();
            }
            $controller = new $controllerName($app);
        } else {
            if (array_key_exists('component', $this->params)) {
                $component = $this->params['component'];
                unset($this->params['component']);
            } else {
                $component = $this->rule->Defaults['component'];
            }
            $controllerName = $this->controller;

            $this->component = CMS_Bazalt::getComponent($component);
            if (!$component) {
                throw new InvalidArgumentException('Invalid component "' . $component . '"');
            }
            $class = $component . '_Controller_' . ucfirst($controllerName);

            $controller = new $class($this->component);
            $conType = typeOf($controller);
            if (!$conType->isSubclassOf('CMS_Component_Controller')) {
                # add class check
                throw new Routing_Exception_InvalidController('Controller "' . $class . '" must extend CMS_Component_Controller');
            }
        }
        return $controller;
    }
}

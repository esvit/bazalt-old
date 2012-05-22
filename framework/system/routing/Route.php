<?php

class Routing_Route extends Object
{
    /**
     * Формат імені функції-екшена
     */
    const ACTION_NAME_FORMAT = '%sAction';

    const CONTROLLER_PARAM_NAME = 'controller';

    const ACTION_PARAM_NAME = 'action';

    const DEFAULT_CONTROLLER_NAME = 'Default';

    const DEFAULT_ACTION_NAME = 'default';

    protected $rule;

    protected $url;
    
    protected $params = array();

    protected $controller = self::DEFAULT_CONTROLLER_NAME;

    protected $action = self::DEFAULT_ACTION_NAME;

    public function param($name, $value = null)
    {
        if (!isset($this->params[$name])) {
            return null;
        }
        if ($value != null) {
            $this->params[$name] = $value;
            return $this;
        }
        return $this->params[$name];
    }

    public function rule($rule = null)
    {
        if ($rule !== null) {
            $this->rule = $rule;
            return $this;
        }
        return $this->rule;
    }

    public function __construct($url, Routing_Rule $rule, $params)
    {
        $this->rule = $rule;
        $this->url = $url;
        $this->params = $params;

        if (array_key_exists(self::CONTROLLER_PARAM_NAME, $this->params)) {
            $this->controller = $this->params[self::CONTROLLER_PARAM_NAME];
            unset($this->params[self::CONTROLLER_PARAM_NAME]);
        }
        if (array_key_exists(self::ACTION_PARAM_NAME, $this->params)) {
            $this->action = $this->params[self::ACTION_PARAM_NAME];
            unset($this->params[self::ACTION_PARAM_NAME]);
        }
    }

    protected function getRouteController()
    {
        $className = $this->controller;

        if (!class_exists($className)) {
             throw new Exception('Class "' . $className . '" not found');
        }
        $type = typeOf($className);
        if (!$type->isSubclassOf('Routing_AbstractController')) {
            throw new Exception('Class "' . $className . '" must extends class AbstractController');
        }
        return $type->createInstance();
    }

    public function dispatch($folder)
    {
        $controller = $this->getRouteController();
        $type = typeOf($controller);
        $className = $type->ClassName;
        $action = sprintf(self::ACTION_NAME_FORMAT, strToLower($this->action));
        if (!$type->hasMethod($action)) {
            throw new Exception('Action "' . $action . '" not found in class "' . $className . '"');
        }

        $args = $type->getMethodArgNames($action);
        $args = array_flip($args);
        $funcArgs = array();

        foreach ($this->params as $name => $param) {
            if (!array_key_exists($name, $args)) {
                throw new Exception('Method "' . $className . '::' . $action . '" must have param' .
                                    ' "' . $name . '" as in route "' . $this->rule->Rule . '"' .
                                    'or set their default value');
            }
            $funcArgs[$name] = $param;
        }
        $fargs = array();
        foreach ($args as $name => $arg) {
            if (array_key_exists($name, $funcArgs)) {
                $fargs []= $funcArgs[$name];
            } else {
                $fargs []= null;
            }
        }

        foreach ($this->rule->PreActions as $callback) {
            call_user_func($callback, $this);
        }

        $controller->preAction($action, $this->params);
        call_user_func_array(array($controller, $action), $fargs);
    }
}

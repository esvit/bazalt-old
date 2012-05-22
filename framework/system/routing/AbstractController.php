<?php

abstract class Routing_AbstractController extends Object
{
    protected $route;

    protected $action;

    protected $arguments;

    public function __construct()
    {
    }

    public function setArguments($args)
    {
        $this->arguments = $args;
    }

    abstract function preAction($action, $args);

    public function init($route, $action)
    {
        $this->route = $route;
        $this->action = $action;
    }

    public function doAction()
    {
        $this->{$this->action}();
    }

    public function getParam($name)
    {
        if (!$this->paramExsist($name)) {
            throw new Exception('Cannot find parameter ' . $name . ' in route');
        }
        return $this->arguments[$name];
    }

    public function paramExsist($name)
    {
        return is_array($this->arguments) && array_key_exists($name, $this->arguments);
    }

    public function getParams()
    {
        return $this->arguments;
    }
}
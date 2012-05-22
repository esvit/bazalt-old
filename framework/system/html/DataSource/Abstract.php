<?php

abstract class Html_DataSource_Abstract
{
    abstract function isPostBack();

    protected $values = array();

    protected $container = null;

    public function __construct(Html_ContainerElement $container, $values = array())
    {
        $this->container = $container;
        $this->values = $values;
    }

    public function values($values = null)
    {
        if ($values !== null) {
            $this->values = $values;
            return $this;
        }
        return $this->values;
    }

    public function value($name, $value = null)
    {
        if ($value !== null) {
            $this->values[$name] = $value;
            return $this;
        }
        if (!isset($this->values[$name])) {
            return null;
        }
        return $this->values[$name];
    }
}
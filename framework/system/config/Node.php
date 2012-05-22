<?php

class Config_Node extends XML_Node
{
    public function parseConfig($namespaces)
    {
        foreach ($namespaces as $class => $namespace) {
            $typeOf = WebConfig::getNamespaceType($class);
            $attrs = $this->getNamespaceAttributes($namespace);
            if (is_array($attrs)) {
                foreach ($attrs as $key => $value) {
                    $args = array($key, $value);
                    $args []= &$this;
                    $typeOf->callStatic('parseConfigAttributes', $args);
                }
            }

            $nsNodes = $this->getNamespaceNodes($namespace);
            if (is_array($nsNodes)) {
                foreach ($nsNodes as $nsNode) {
                    $nsNode->parseConfig($namespaces);
                    $typeOf->callStatic('parseConfigNode', array($this, $nsNode));
                }
            }
        }
        foreach ($this->nodes() as $node) {
            $node->parseConfig($namespaces);
            self::configurationObject($node->name(), $node);
        }
    }

    protected static function configurationObject($class, $config)
    {
        Configuration::$instances[$class] = $config;
        //return;
        if (!class_exists($class)) {
            return;
        }

        $type = typeOf($class);
        if (!$type || $type == 'string' || !$type->hasInterface('IWebConfig')) {
            return;
        }
        $obj = Object::Singleton($class);
        if (!$obj) {
            throw new Exception('Invalid singleton object of class "' . $class . '"');
        }
        $obj->loadWebConfig($config);
    }
}
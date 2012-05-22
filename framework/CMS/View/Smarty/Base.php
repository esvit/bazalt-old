<?php

using('Framework.Vendors.Smarty');

class CMS_View_Smarty_Base extends Smarty implements IWebConfig
{
    protected static $instance = null;

    public function __construct()
    {
        parent::__construct();

        $this->debugging = false;
        $this->caching = true;
        $this->use_sub_dirs = true;
        $this->cache_lifetime = 120;

        //$this->caching_type = 'eaccelerator';
        $this->compile_dir = TEMP_DIR . '/templates/Smarty/';

        $this->addPluginsLocation(dirname(__FILE__) . '/plugins/');
    }

    private static $allowAttributes = array(
        'template_dir',
        'compile_dir',
        'force_compile',
        'plugins_dir',
        'caching',
        'cache_dir',
        'cache_lifetime',
        'cache_handler_func',
        'compile_check'
    );

    public static function &getInstance()
    {
        if (self::$instance == null) {
            $className = __CLASS__;
            self::$instance = new $className;
        }
        return self::$instance;
    }

    public function addPluginsLocation($location)
    {
        $this->plugins_dir[] = $location;
    }

    public function loadWebConfig($node)
    {
        $this->debugging = false;
        $this->caching = true;
        $this->use_sub_dirs = true;
        $this->cache_lifetime = 120;

        foreach ($node as $elem) {
            $name = $elem->name();
            $value = DataType_String::replaceConstants($elem->value());
            if (!in_array($name, self::$allowAttributes)) {
                throw new Exception('Denied attribute ' . $name);
            }
            if ($value == 'false') {
                $value = false;
            }
            if ($value == 'true') {
                $value = true;
            }
            if ($name == 'plugins_dir') {
                $this->addPluginsLocation($value);
            } else {
                $this->$name = $value;
            }
        }
        return $this;
    }
}
<?php

using('Framework.System.Xml');

Core_Autoload::registerNamespace('Config', dirname(__FILE__));

class ConfigNode extends Config_Node {}
interface IConfigParser extends Config_IParser {}

interface Config_IConfigurable
{
    function configure($config);
}

require_once 'Configuration.php';
require_once 'webconfig.class.inc';
require_once 'webconfigcomplier.class.inc';
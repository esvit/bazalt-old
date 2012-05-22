<?php

class WebConfig extends Object implements Config_IParser
{
    const CONSTANTS_NODE_NAME = 'constants';

    const INCLUDE_NODE_NAME = 'include';

    const NAMESPACE_CONFIG_ATTRIBUTE = 'namespace';

    const STAGE_CONFIG_ATTRIBUTE = 'stage';

    const CONFIG_NODE_CLASS = 'Config_Node';

    protected static $currentConfig;

    protected static $namespaces = null;

    public static function load($fileName)
    {
        self::$namespaces = null;
        self::$currentConfig = self::parseConfigFile($fileName);

        return self::$currentConfig;
    }

    public static function parseConfigNode(Config_Node $node, Config_Node $nsNode)
    {
        $nodeName = $nsNode->name();
        switch ($nodeName) {
            case self::CONSTANTS_NODE_NAME:
                foreach ($nsNode->nodes() as $const) {
                    if (defined($const->name())) {
                        throw new Exception('Constant ' . $const->name() . ' alredy defined');
                    }
                    define($const->name(), DataType_String::replaceConstants($const->value()));
                }
                break;
            case self::INCLUDE_NODE_NAME:
                $file = $nsNode->attribute('file');
                $realfile = realpath(DataType_String::replaceConstants($file));
                if (empty($realfile)) {
                    throw new Exception('Invalide include config file ' . $file);
                }
                self::parseConfigFile($realfile);
                break;
        }
    }

    public static function parseConfigAttributes($key, $value, Config_Node &$node)
    {
        switch ($key) {
            case self::NAMESPACE_CONFIG_ATTRIBUTE:
                using($value);
                break;
            case self::STAGE_CONFIG_ATTRIBUTE:
                $stage = strToLower($value);
                if (STAGE != $stage) {
                    $node->remove();
                }
                break;
        }
    }

    protected static function loadConstants()
    {
    
    }

    public static function getNamespaceType($class)
    {
        $typeOf = typeOf($class);
        if (!$typeOf || $typeOf == 'string') {
            throw new Exception('Class "' . $class . '" not found (config <xmlns>)');
        }
        if (!$typeOf || $typeOf == 'string' || !$typeOf->hasInterface('Config_IParser')) {
            throw new Exception('Invalid config parser "' . $class . '", config must implements Config_IParser interface');
        }
        return $typeOf;
    }

    protected static function parseNamespaces($config)
    {
        $namespaces = array();
        $ns = $config->node('xmlns');
        if (!$ns) {
            return array();
        }
        
        foreach ($ns as $namespace) {
            $namespaces[$namespace->name()] = $namespace->value();
            $internalNamespace = strToLower($namespace->value());
            if (substr($internalNamespace, 0, 4) == 'urn:') {
                $internalNamespace = substr($internalNamespace, 4);
                using($internalNamespace);
            }
        }
        $ns->remove();
        return $namespaces;
    }

    protected static function parseConfigFile($fileName)
    {
        if(defined('COMPLIE_CONFIGS') && COMPLIE_CONFIGS === true) {
            $config = WebConfigCompiler::getConfig($fileName);
        } else {
            $cacheKey = md5('config' . $fileName . filemtime($fileName));
            $config = Cache::Singleton()->getCache($cacheKey);
        }
        if (!$config) {
            try {
                $config = XML_Parser::parse($fileName, self::CONFIG_NODE_CLASS);
            } catch (Exception $ex) {
                throw new Exception('Invalid xml file ' . $fileName);
            }
            if(defined('COMPLIE_CONFIGS') && COMPLIE_CONFIGS === true) {
                WebConfigCompiler::compile($fileName, $config);
            } else {
                Cache::Singleton()->setCache($cacheKey, clone $config);
            }
        }

        if (self::$namespaces == null) {
            self::$namespaces = self::parseNamespaces($config);
        }

        $config->parseConfig(self::$namespaces);
        return $config;
    }
}
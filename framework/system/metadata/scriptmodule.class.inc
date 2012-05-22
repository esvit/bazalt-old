<?php

/**
 * @todo add delete function
 */
class ScriptModule
{
    protected static $modules = null;

    const MODULE_FILE = 'package.cfg';

    const ALL_MODULES_FILE = 'modules.xml';

    public static function getBaseFolder()
    {
        return JSLIBS_DIR;
    }

    /**
     * Parse file with list of modules
     * Return array of modules:
     * <code>
     * Array
     * (
     *     [jquery] => Array
     *         (
     *             [name] => jquery
     *             [default] => 1.4.2
     *             [versions] => Array
     *                 (
     *                     [1.4.2] => jquery-1.4.2
     *                     [1.4.4] => jquery-1.4.4
     *                 )
     *
     *         )
     * )
     *
     * @return array
     */
    public static function parseModulesGlobalFile()
    {
        if (self::$modules != null) {
            return self::$modules;
        }

        $path = self::getBaseFolder() . '/' . self::ALL_MODULES_FILE;
        $res = array();
        if (file_exists($path)) {
            if(defined('COMPLIE_CONFIGS') && COMPLIE_CONFIGS === true) {
                $res = self::getCompiledModule($path);
            } else {
                $cacheKey = __FUNCTION__ . $path . filemtime($path);
                $res = Cache::Singleton()->getCache($cacheKey);
            }

            if (!$res) {
                $xml = XmlParser::parse($path);
                if (!$xml) {
                    throw new Exception('Invalid xml file ' . $path);
                }
                // read modules
                foreach ($xml->nodes() as $child) {
                    $name = strToLower($child->attribute('name'));
                    $res[$name] = array(
                        'name' => $name,
                        'default' => $child->attribute('default'),
                        'versions' => array()
                    );
                    $mod = &$res[$name];

                    // read versions
                    foreach ($child->nodes('version') as $version) {
                        $v = $version->attribute('v');
                        if (empty($mod['default'])) {
                            $mod = $v;
                        }
                        $mod['versions'][$v] = $version->attribute('folder');
                    }
                }
                if(defined('COMPLIE_CONFIGS') && COMPLIE_CONFIGS === true) {
                    self::compile($path, $res);
                } else {
                    // save cache
                    Cache::Singleton()->setCache($cacheKey, $res, false, array('js-modules'));
                }
            }
        }
        self::$modules = $res;
        return $res;
    }

    public static function getModule($name, $version = null)
    {
        $name = strToLower($name);

        $cacheKey = __CLASS__ . __FUNCTION__ . $name . $version;
        $res = Cache::Singleton()->getCache($cacheKey);
        if ($res) {
            return $res;
        }

        if (self::$modules == null) {
            self::parseModulesGlobalFile();
        }
        if (!isset(self::$modules[$name])) {
            throw new Exception('Module with name "' . $name . '" not found (' . self::getBaseFolder() . ')');
        }
        $module = self::$modules[$name];

        if ($version == null) {
            $version = $module['default'];
        }
        if (count($module['versions']) == 0) {
            throw new Exception('JS lib haven\'t versions ' . $path);
        }
        $moduleFile = self::parseModuleFile($module['versions'][$version], $module['name']);

        Cache::Singleton()->setCache($cacheKey, $moduleFile, false, array('js-module'));
        return $moduleFile;
    }

    public static function parseModuleFile($folder, $name)
    {
        $name = strToLower($name);
        $path = self::getBaseFolder() . '/' . $folder . '/' . self::MODULE_FILE;
        $res = null;
        if (file_exists($path)) {
            if(defined('COMPLIE_CONFIGS') && COMPLIE_CONFIGS === true) {
                $res = self::getCompiledModule($path);
            } else {
                $cacheKey = __FUNCTION__ . $path . filemtime($path);
                $res = Cache::Singleton()->getCache($cacheKey);
            }

            if (!$res) {
                $xml = XmlParser::parse($path);
                $version = $xml->attribute('version');
                $moduleName = strToLower($xml->attribute('name'));

                if ($moduleName != $name) {
                    throw new Exception ('Module "' . $name . '" not found. Present module ' . $moduleName);
                }
                $res = array('scripts'  => array (),
                             'styles' => array (),
                             'require' => array(),
                             'head' => '',
                             'version' => $version);

                foreach($xml as $child) {
                    if ($child->name() == 'head') {
                        $res['head'] = $child->value();
                        continue;
                    }
                    foreach($child as $file) {
                        if($child->name() == 'require'){
                            $name = $file->attribute('name');
                            $version = $file->attribute('version');
                            $res["require"][$name] =  $version;
                            continue;
                        }
                        $compress = $file->attribute('compress') != 'false';
                        $file = $file->value();
                        if (empty($file)) {
                            continue;
                        }
                        if ($file[0] != '/') {
                            $file = self::getBaseFolder() . '/' . $folder . '/' . $file;
                        }
                        $file = array('file' => $file, 'compress' => $compress);
                        if ($child->name() == 'scripts') {
                            $res['scripts'][] = $file;
                        } else {
                            $res['styles'][] = $file;
                        }
                    }
                }
                
                if(defined('COMPLIE_CONFIGS') && COMPLIE_CONFIGS === true) {
                    self::compile($path, $res);
                } else {
                    Cache::Singleton()->setCache($cacheKey, $res);
                }
            }
        } else {
            throw new Exception('File not found ' . $path);
        }
        return $res;
    }
    
    protected static function getCompiledFileName($path)
    {
        return CACHE_DIR.'/'.md5('compiled_module_' . $path . filemtime($path)).'.php';
    }
    
    protected static function getCompiledFunctionName($path)
    {
        return preg_replace('/\W/i', '', 'compiled_module_' . $path);
    }
    
    protected static function getCompiledModule($path)
    {
        $compiledFile = self::getCompiledFileName($path);
        if(!file_exists($compiledFile)) {
            return false;
        } else {
            require_once $compiledFile;
        }
        $function = self::getCompiledFunctionName($path);
        if(!function_exists($function)) {
            return false;
        }
        return call_user_func($function);
    }
    
    protected static function compile($path, $res)
    {
        $function = self::getCompiledFunctionName($path);
        file_put_contents(self::getCompiledFileName($path),
                "<?php
                function $function(){ 
                    return ".var_export($res, true).";
                }"
        );
    }
}

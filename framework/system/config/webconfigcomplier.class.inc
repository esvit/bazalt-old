<?php

class WebConfigCompiler// extends Object implements IConfigParser
{
    protected static function getCompiledFileName($fileName)
    {
        return CACHE_DIR.'/'.md5('compiled_config_' . $fileName . filemtime($fileName)).'.php';
    }
    
    protected static function getCompiledFunctionName($fileName)
    {
        return preg_replace('/\W/i', '', 'compiled_config_' . $fileName);
    }
    
    public static function getConfig($fileName)
    {
        $compiledFile = self::getCompiledFileName($fileName);
        if(!file_exists($compiledFile)) {
            return false;
        } else {
            require_once $compiledFile;
        }
        $function = self::getCompiledFunctionName($fileName);
        if(!function_exists($function)) {
            return false;
        }
        return call_user_func($function);
    }
    
    public static function compile($fileName, $config)
    {
        $class = get_class($config);
        $function = self::getCompiledFunctionName($fileName);
        file_put_contents(
            self::getCompiledFileName($fileName),
            '<?php' . "\n" .
            'function ' . $function . '() {' . "\n" .
            '    return ' . $class . '::__fromArray(' . var_export($config->__toArray(), true) . ', "' . $class . '");' . "\n" .
            '}'
        );
    }
}
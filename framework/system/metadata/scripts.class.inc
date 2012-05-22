<?php

/**
 * @todo add delete function
 */
class Scripts
{
    const SCRIPTINLINE_HTMLTAG = '<script type="text/javascript">%s</script>';

    protected static $modules = array();

    protected static $inlineJs = array();

    /**
     * Додає скрипт до списку скриптів
     */
    public static function add($file, $name = null, $compress = true)
    {
        Assets_JS::add($file, $compress);
    }

    public static function addInline($js, $name = null)
    {
        if (STAGE == PRODUCTION_STAGE) {
            $js = JSCompressor::compressFile($js);
        }
        if (is_null($name)) {
            self::$inlineJs []= $js;
        } else {
            self::$inlineJs[$name] = $js;
        }
    }

    public static function getInlineScript()
    {
        if(count(self::$inlineJs) == 0) {
            return '';
        }
        $jsCode = '';
        foreach (self::$inlineJs as $js) {
            $jsCode .= $js . "\n";
        }
        return sprintf(self::SCRIPTINLINE_HTMLTAG, $jsCode);
    }

    /**
     * Add javascript library
     *
     * @param string $name Name of library
     * @param string $version (optional) Version of library
     * @return void
     */
    public static function addModule($name, $version = null)
    {
        return Assets_JS::addPackage($name, $version);
    }
}
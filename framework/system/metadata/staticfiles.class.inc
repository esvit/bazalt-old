<?php

class StaticFiles
{
    protected static $saveDir = COMPILED_DIR;

    protected static $cacheSalt = '';

    public static function setCacheSalt($salt)
    {
        self::$cacheSalt = $salt;
    }

    public static function getCacheSalt()
    {
        return self::$cacheSalt;
    }

    /**
     * Copy file to public dir
     */
    public static function copy($file)
    {
        return Assets_File::getInstance()->copy($file);
    }

    public static function savePrecompiledFile($fileOut, $files = array(), $type = null)
    {
        $fileContent = $type;
        foreach ($files as $file) {
            $fileContent .= "\n" . $file;
        }
        file_put_contents($fileOut . '.pre', $fileContent);
    }
}

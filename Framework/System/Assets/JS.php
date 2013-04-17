<?php

namespace Framework\System\Assets;

class JS
{
    const SCRIPTFILE_HTMLTAG = '<script type="text/javascript" src="%s"></script>';

    protected static $packages = array();

    /**
     * Add style file
     *
     * @param string $file      Filename
     * @param string $condition Condition (optional)
     * @return void
     */
    public static function add($file, $condition = null)
    {
        FileManager::getInstance()->add($file, 'js', $condition);
    }

    public static function getFiles()
    {
        return FileManager::getInstance()->getFiles('js');
    }

    public static function getHtml()
    {
        $files = self::getFiles();
        //$files = Assets_CSS::getInstance()->modifyFiles($files);

        $html = '';
        foreach ($files as $condition => $fileList) {
            foreach ($fileList as $file) {
                $isUrl = (strToLower(substr($file, 0, 4)) == 'http');
                if (!$isUrl) {
                    $file = FileManager::getInstance()->getRelativePath($file);
                }

                $html .= sprintf(self::SCRIPTFILE_HTMLTAG, $file). "\n";
            }
        }
        return $html;
    }

    /**
     * Add javascript library
     *
     * @param string $name Name of library
     * @param string $version (optional) Version of library
     * @return void
     */
    public static function addPackage($name, $version = null)
    {
        if (isset(self::$packages[$name])) {
            return;
        }
        $package = Package::getInstance()->getPackage($name, $version);

        if (!$package) {
            throw new \Exception('Package with name "' . $name . '" not found!');
        }

        self::$packages[$name] = empty($version) ? '-' : $version;

        $package->connect();
    }
}
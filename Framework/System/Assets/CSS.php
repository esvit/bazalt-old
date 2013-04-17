<?php

namespace Framework\System\Assets;

class CSS
{
    const STYLEFILE_HTMLTAG = '<link rel="stylesheet" type="text/css" href="%s" />';

    /**
     * Add style file
     *
     * @param string $file      Filename
     * @param string $condition Condition (optional)
     * @return void
     */
    public static function add($file, $condition = null)
    {
        FileManager::getInstance()->add($file, 'css', $condition);
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

                $html .= sprintf(self::STYLEFILE_HTMLTAG, $file). "\n";
            }
        }
        return $html;
    }

    public static function getFiles()
    {
        return FileManager::getInstance()->getFiles('css');
    }
}
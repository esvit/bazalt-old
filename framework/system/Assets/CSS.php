<?php

class Assets_CSS
{
    const STYLEFILE_HTMLTAG = '<link rel="stylesheet" href="%s" type="text/css" />';

    /**
     * Add style file
     *
     * @param string $file      Filename
     * @param string $condition Condition (optional)
     * @return void
     */
    public static function add($file, $condition = null)
    {
        Assets_FileManager::getInstance()->add($file, 'css', $condition);
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
                    $file = Assets_FileManager::getInstance()->getRelativePath($file);
                }

                $html .= sprintf(self::STYLEFILE_HTMLTAG, $file). "\n";
            }
        }
        return $html;
    }

    public static function getFiles()
    {
        return Assets_FileManager::getInstance()->getFiles('css');
    }
}
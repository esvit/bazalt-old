<?php

using('Framework.Vendors.LessPHP');

class Assets_Filter_Less extends Assets_Filter_Abstract
{
    protected static $currentFile;

    public function prepareFiles(array $files)
    {
        foreach ($files as $attrs) {
            $file = $attrs['file'];
            if (pathinfo($file, PATHINFO_EXTENSION) == 'less') {
                $cssFile = Assets_FileManager::filename($file) . '.css';
                if (!file_exists($cssFile)) {
                    lessc::ccompile($file, $cssFile);
                    $css = file_get_contents($cssFile);
                    self::$currentFile = $file;

                    $cssReplaced = preg_replace_callback("/url\s*\((.*)\)/siU", array('self', 'replaceImageRelativePath'), $css);
                    if ($cssReplaced !== null) {
                        $css = $cssReplaced;
                    }
                    file_put_contents($cssFile, $css);
                }
                Assets_FileManager::getInstance()->replace($file, $cssFile, 'css');
            }
        }
    }

    protected static function getFullPath($file, $forUrl = true)
    {
        $file = trim($file, '"\'');
        if ($file[0] != '/') {
            if ($forUrl) {
                $file = self::$cssPath . '/' . $file;
            } else {
                $file = dirname(self::$currentFile) . '/' . $file;
            }
        } else if (!$forUrl) {
            $file = SITE_DIR . $file;
        }
        return $file;
    }

    protected static function replaceImageRelativePath(array $match)
    {
        $match[1] = trim($match[1], '"\'');
        if (substr($match[1], 0, 4) == 'http') {
            return 'url("' . $match[1] . '")';
        }
        $file = self::getFullPath($match[1], false);
        $additions = '';
        if (($pos = strpos($file, '?')) !== false || ($pos = strpos($file, '?')) !== false) {
            $additions = substr($file, $pos);
            $file = substr($file, 0, $pos);
        }

        if (!file_exists($file)) {
            return $match[0];
        }
        if (OS == OS_WIN) {
            $filePath = Assets_FileManager::copy($file);
        } else {
            $filePath = Assets_FileManager::link($file);
        }
        return 'url("' . relativePath($filePath) . $additions . '")';
    }

    public function modifyFiles(array $files)
    {
    }
}
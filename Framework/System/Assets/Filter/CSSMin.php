<?php

namespace Framework\System\Assets\Filter;

class CSSMin extends AbstractFilter
{
    const REMOVE_LAST_SEMICOLON = 1;

    const REPLACE_RELATIVE_PATH = 2;

    const REPLACE_WITH_COOKIELESS_DOMAIN = 4;

    const REPLACE_DATA_URI = 8;

    const COMPRESS_CSS = 16;

    const SPLIT_FILES = 32;

    const INCLUDE_IMPORT = 64;

    protected $options = 0;
    
    protected static $cssPath;

    protected static $currentFile = null;

    protected static $currentOptions = 5;

    protected static $cookieLessDomain = null;

    public function prepareFiles(array $files)
    {
        $hash = '';
        foreach ($files as $key => $attrs) {
            $file = $attrs['file'];
            $isUrl = (strToLower(substr($file, 0, 4)) == 'http');
            if (!$isUrl && pathinfo($file, PATHINFO_EXTENSION) == 'css') {
                $hash .= $file . filemtime($file);
                Assets_FileManager::getInstance()->remove($key, 'css');
            }
        }
        $cssFile = Assets_FileManager::filename(md5($hash)) . 'css';
        if (!file_exists($cssFile) || (isset($this->config['alwaysRecompile']) && $this->config['alwaysRecompile'])) {
            $cssAll = '';
            foreach ($files as $attrs) {
                $file = $attrs['file'];
                $isUrl = (strToLower(substr($file, 0, 4)) == 'http');
                if (!$isUrl && pathinfo($file, PATHINFO_EXTENSION) == 'css') {
                    $css = file_get_contents($file);

                    if (STAGE == DEVELOPMENT_STAGE) {
                        $cssAll .= '/* ' . $file . " */\n";
                    }
                    $cssAll .= self::minify($css, $file) . "\n";
                }
            }
            file_put_contents($cssFile, $cssAll);
        }
        Assets_FileManager::getInstance()->add($cssFile, 'css');
    }

    public function modifyFiles(array $files)
    {
    }

    public static function setCssPath($path)
    {
        self::$cssPath = $path;
    }

    public static function getCssPath()
    {
        return self::$cssPath;
    }

    public static function setCookieLessDomain($domain)
    {
        self::$cookieLessDomain = $domain;
    }

    public static function getCookieLessDomain()
    {
        return self::$cookieLessDomain;
    }

    /**
     * Minifies stylesheet definitions
     *
     * <code>
     * $cssMinified = CSSCompressor::minify(file_get_contents("path/to/target/file.css"));
     * </code>
     * 
     * @param string       $css     Stylesheet definitions as string
     * @param array|string $options Array or comma speperated list of options:
     *                              - REMOVE_LAST_SEMICOLON: Removes the last semicolon in 
     *                                the style definition of an element (activated by default).
     *
     *                              - REPLACE_RELATIVE_PATH: Replace relative image path with
     *                                absolute path;
     * @return string Minified stylesheet definitions
     */
    public static function minify($css, $cssFile, $options = null)
    {
        //$css = file_get_contents($cssFile);
        if ($options == null) {
            $options = self::REPLACE_RELATIVE_PATH | self::REMOVE_LAST_SEMICOLON | self::COMPRESS_CSS | self::INCLUDE_IMPORT;
            if (self::$cookieLessDomain != null) {
                $options |= self::REPLACE_WITH_COOKIELESS_DOMAIN;
            }
        }
        self::$currentOptions = $options;
        self::$currentFile = $cssFile;

        if ($options & self::REPLACE_DATA_URI && !CMS_Request::isIE()) {
            # Encode url() to base64
            $cssReplaced = preg_replace_callback("/url\s*\((.*)\)/siU", array('self', 'encodeUrl'), $css);
            if ($cssReplaced !== null) {
                $css = $cssReplaced;
            }
        } else if ($options & self::REPLACE_RELATIVE_PATH) {
            # Replace relative path
            $cssReplaced = preg_replace_callback("/url\s*\((.*)\)/siU", array('self', 'replaceImageRelativePath'), $css);
            if ($cssReplaced !== null) {
                $css = $cssReplaced;
            }
        }
        if ($options & self::COMPRESS_CSS) {
            # Replace CR, LF and TAB to spaces
            $css = str_replace(array("\n", "\r", "\t"), " ", $css);

            # Replace multiple to single space
            $cssReplaced = preg_replace("/\s\s+/", " ", $css);
            if ($cssReplaced !== null) {
                $css = $cssReplaced;
            }
            # Remove unneeded spaces
            $cssReplaced = preg_replace("/\s*({|}|=|~|\+|>|\||;|:|,)\s*/", "$1", $css);
            if ($cssReplaced !== null) {
                $css = $cssReplaced;
            }
            if ($options & self::REMOVE_LAST_SEMICOLON) {
                # Removes the last semicolon of every style definition
                $css = str_replace(";}", "}", $css);
            }
            # Remove comments
            $cssReplaced = preg_replace("/\/\*[\d\D]*?\*\/|\t+/", " ", $css);
            if ($cssReplaced !== null) {
                $css = $cssReplaced;
            }
            # Minimize hex colors
            $cssReplaced = preg_replace('/([^=])#([a-f\\d])\\2([a-f\\d])\\3([a-f\\d])\\4([\\s;\\}])/i', '$1#$2$3$4$5', $css);
            if ($cssReplaced !== null) {
                $css = $cssReplaced;
            }
        }
        return trim($css);
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
        return realpath($file);
    }

    protected static function replaceImageRelativePath(array $match)
    {
        $match[1] = trim($match[1], '"\'');
        if (substr($match[1], 0, 4) == 'http') {
            return 'url("' . $match[1] . '")';
        }
        $file = $match[1];
        $additions = '';
        if (($pos = strpos($file, '?')) !== false || ($pos = strpos($file, '#')) !== false) {
            $additions = substr($file, $pos);
            $file = substr($file, 0, $pos);
        }
        $file = self::getFullPath($file, false);

        if (!file_exists($file)) {
            return $match[0];
        }
        if (OS == OS_WIN) {
            $filePath = Assets_FileManager::copy($file);
        } else {
            $filePath = Assets_FileManager::link($file);
        }

        if (self::$currentOptions & self::REPLACE_WITH_COOKIELESS_DOMAIN && self::$cookieLessDomain != null) {
            $filePath = 'http://' . self::$cookieLessDomain . relativePath($filePath) . $additions;
        }
        return 'url("' . relativePath($filePath) . $additions . '")';
    }

    /**
     * Encodes a url() expression.
     *
     * @param       array   $match
     * @return      string
     */
    protected static function encodeUrl($match)
    {
        $data = $match[1];
        if (substr($match[1], 0, 4) != 'data') {
            $file = self::getFullPath($match[1], false);
            if (file_exists($file)) {
                if(filesize($file) < 10240) {// 10 Kb
                    $info = getimagesize($file);
                    $mime = image_type_to_mime_type($info[2]);

                    $content = base64_encode(file_get_contents($file));
                    if (!empty($content)) {
                        $data = 'data:' . $mime . ';base64;00,' . $content;
                    }
                } else {
                    return self::replaceImageRelativePath($match);
                }
            } else {
                return $match[0];
            }
        }
        return 'url(\'' . $data . '\')';
    }
}
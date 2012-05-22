<?php

class Styles extends Object implements IEventable
{
    public $eventOnCompileFile = Event::EMPTY_EVENT;

    public $eventOnCompileComplete = Event::EMPTY_EVENT;

    const STYLEFILE_HTMLTAG = '<link rel="stylesheet" href="%s" type="text/css" />';

    const STYLEINLINE_HTMLTAG = '<style type="text/css">%s</style>';

    const PRECOMILE_TYPE = '[Styles]';

    const NO_CONDITION = 'no_condition';

    protected static $cookielessDomain = null;
    
    protected static $styles = array();

    protected static $inlineStyles = array();

    /**
     * Add style file
     *
     * @param string $file Filename
     * @param string $name Name (optional)
     * @return void
     */
    public static function add($file, $name = null, $condition = null)
    {
        Assets_CSS::Singleton()->add($file, $name, $condition);
    }

    public static function addInline($css, $name = null)
    {
        Assets_CSS::Singleton()->addInline($css, $name);
    }


    public static function getStyles($condition = null)
    {
        if ($condition == null) {
            $condition = self::NO_CONDITION;
        }
        return self::$styles[$condition];
    }

    public static function compileFiles($files = array(), $toFile)
    {
        $options = CSSCompressor::COMPRESS_CSS | CSSCompressor::REMOVE_LAST_SEMICOLON;// | # remove last ;
                   //CSSCompressor::REPLACE_DATA_URI;  # replace a relative path absolute

        //if (CMS_Request::isIE()) {
            $options |= CSSCompressor::REPLACE_RELATIVE_PATH;
        //}

        if (self::$cookielessDomain != null) {
            # Set cookieless domain
            CSSCompressor::setCookieLessDomain(self::$cookielessDomain);
            $options |= CSSCompressor::REPLACE_WITH_COOKIELESS_DOMAIN; # add cookieless domain
        }

        $fp = fopen($toFile, 'w');
        foreach ($files as $styleFile) {
            $styleFile = trim($styleFile);
            if (file_exists($styleFile)) {
                $dir = relativePath(dirname($styleFile));

                # set absolute path to css file
                CSSCompressor::setCssPath($dir);
                $css = file_get_contents($styleFile);

                $args = array();
                $args['file'] = $styleFile;
                $args['content'] = &$css;
                Event::trigger('Styles', 'OnCompileFile', $args);
                # minify css file
                $styleContent = CSSCompressor::minify($css, $styleFile, $options);

                if (STAGE == DEVELOPMENT_STAGE) {
                    fwrite($fp, '/* ' . $styleFile . ' */' . "\n");
                }
                fwrite($fp, $styleContent);
                fwrite($fp, "\n");
            }
        }
        fclose($fp);

        if (function_exists('gzencode')) {
            $fp = fopen($toFile . '.gz', 'w');
            $content = file_get_contents($toFile);
            $content = gzencode($content, 9);
            fwrite($fp, $content);
            fclose($fp);
        }
    }

    public static function CookielessDomain($domain = null)
    {
        if ($domain == null) {
            return self::$cookielessDomain;
        }
        self::$cookielessDomain = $domain;
    }

    /**
     * Get hash for current scripts
     *
     * @return string hashkey
     */
    protected static function getHashKeys()
    {
        $keys = array();

        foreach (self::$styles as $condition => $files) {
            foreach (array_unique($files) as $file) {
                if (substr($file, 0, 4) != 'http' && file_exists($file)) {
                    if (!isset($keys[$condition])) {
                        $keys[$condition] = '';
                    }
                    $keys[$condition] .= $file . filemtime($file);
                } else if (substr($file, 0, 4) == 'http') {
                    $keys[$condition] .= $file;
                } else {
                    throw new Exception('File "' . $file . '" does not exists');
                }
            }
        }

        foreach ($keys as $condition => $key) {
            $keys[$condition] = md5($condition . self::$cookielessDomain . $key);

            if (CMS_Request::isIE()) {
                $keys[$condition] .= '_ie';
            }
        }
        return $keys;
    }

    public static function mergeStyles()
    {
        // if (count(self::$styles) == 0) {
            // return '';
        // }
        $keys = self::getHashKeys();

        $files = array();
        foreach ($keys as $condition => $key) {
            $file = StaticFiles::getFilename($key . '.css');

            if (!file_exists($file)) {
                StaticFiles::savePrecompiledFile($file, array_unique(self::$styles[$condition]), self::PRECOMILE_TYPE);
            }
            $files[$condition] = relativePath($file);
            if (self::$cookielessDomain) {
                $files[$condition] = 'http://' . self::$cookielessDomain . $files[$condition];
            }
        }
        return $files;
    }

    public static function getHtml()
    {
        return Assets_CSS::Singleton()->toString();
        //if (!isset($_GET['full'])) {//&& STAGE == DEVELOPMENT_STAGE
            $html = '';
            $files = self::mergeStyles();
            foreach ($files as $condition => $file) {
                if ($condition == self::NO_CONDITION) {
                    $html .= sprintf(self::STYLEFILE_HTMLTAG, $file);
                } else {
                    $html .= "\n<!--[if " . $condition . "]>\n" . sprintf(self::STYLEFILE_HTMLTAG, $file) . "\n<![endif]-->";
                }
            }

            if(count(self::$inlineStyles) > 0) {
                $cssCode = '';
                foreach (self::$inlineStyles as $css) {
                    $cssCode .= $css . "\n";
                }
                $html .= sprintf(self::STYLEINLINE_HTMLTAG, $cssCode);
            }
            return $html;
        /*}

        $ret = '';
        $styles = self::getStyles();
        foreach ($styles as $style) {
            $ret .= sprintf(self::STYLEFILE_HTMLTAG, $style) . "\n";
        }

        return $ret;*/
    }
}
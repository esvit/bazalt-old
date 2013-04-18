<?php

namespace Framework\CMS\Http;

use Framework\Core\Helper\Url;
use Framework\Core\Helper\ArrayHelper;
use Framework\System\Session\Session;

if (!defined('ENABLE_CSRF_PROTECTION')) {
    define('ENABLE_CSRF_PROTECTION', true);
}

class Request
{
    public function __construct()
    {
        self::removeSlashes();
    }

    public static function url()
    {
        return Url::getRequestUrl();
    }

    public static function getSupportedMimeType($mimeTypes = null)
    {
        // Values will be stored in this array
        $acceptTypes = Array ();

        // Accept header is case insensitive, and whitespace isn’t important
        $accept = strtolower(str_replace(' ', '', $_SERVER['HTTP_ACCEPT']));
        // divide it into parts in the place of a ","
        $accept = explode(',', $accept);
        foreach ($accept as $a) {
            // the default quality is 1.
            $q = 1;
            // check if there is a different quality
            if (strpos($a, ';q=')) {
                // divide "mime/type;q=X" into two parts: "mime/type" i "X"
                list($a, $q) = explode(';q=', $a);
            }
            // mime-type $a is accepted with the quality $q
            // WARNING: $q == 0 means, that mime-type isn’t supported!
            $acceptTypes[$a] = $q;
        }
        arsort($acceptTypes);

        // if no parameter was passed, just return parsed data
        if (!$mimeTypes) return $acceptTypes;

        $mimeTypes = array_map('strtolower', (array)$mimeTypes);

        // let’s check our supported types:
        foreach ($acceptTypes as $mime => $q) {
           if ($q && in_array($mime, $mimeTypes)) return $mime;
        }
        // no mime-type found
        return null;
    }

    /**
     * Видаляє слешування, якщо це потрібно
     */
    protected static function removeSlashes()
    {
        if ((function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) || ini_get('magic_quotes_sybase')) {
            $_POST    = ArrayHelper::deepArrayMap('stripslashes', $_POST);
            $_GET     = ArrayHelper::deepArrayMap('stripslashes', $_GET);
            $_COOKIE  = ArrayHelper::deepArrayMap('stripslashes', $_COOKIE);
            $_REQUEST = ArrayHelper::deepArrayMap('stripslashes', $_REQUEST);
        }
    }

    public static function isAjax()
    {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest');
    }

    public static function isIE()
    {
        return (isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false));
    }
}
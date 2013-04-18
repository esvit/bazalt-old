<?php

namespace Framework\CMS;

use Framework\Core\Helper\Url;
use Framework\System\Session\Session;

if (!defined('ENABLE_CSRF_PROTECTION')) {
    define('ENABLE_CSRF_PROTECTION', true);
}

class Request
{
    const CSRF_TOKEN_FIELD = '_csrf_token_';

    public function url()
    {
        return Url::getRequestUrl();
    }

    public function __construct()
    {
        self::removeSlashes();
    }

    /**
     * Генерує Csrf токен для захисту від міжсайтових запитів
     *
     * @param string $value
     * @return string
     */
    public static function generateCsrfToken($value = '')
    {
        $key = Session::getSessionId();
        $key .= $value;
        $key .= CMS_Bazalt::getSecretKey();

        return md5($key);
    }

    public static function getCsrfTokenName($id = '')
    {
        return md5(self::CSRF_TOKEN_FIELD . $id);
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

            $_POST = DataType_Array::deepArrayMap('stripslashes', $_POST);
            $_GET = DataType_Array::deepArrayMap('stripslashes', $_GET);
            $_COOKIE = DataType_Array::deepArrayMap('stripslashes', $_COOKIE);
            $_REQUEST = DataType_Array::deepArrayMap('stripslashes', $_REQUEST);
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
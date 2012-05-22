<?php

if (!defined('ENABLE_CSRF_PROTECTION')) {
    define('ENABLE_CSRF_PROTECTION', true);
}

class CMS_Request extends Object implements ISingleton
{
    const CSRF_TOKEN_FIELD = '_csrf_token_';

    protected static $requestCacheTime = 0;

    protected static $requestCacheETag = '';

    public static function getCacheTime()
    {
        return self::$requestCacheTime;
    }

    public static function getCacheETag()
    {
        return self::$requestCacheETag;
    }

    public function url()
    {
        return Url::getRequestUrl();
    }

    public function __construct()
    {
        parent::__construct();

        self::removeSlashes();

        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
            self::$requestCacheTime = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
        }

        if (isset($_SERVER['HTTP_IF_NONE_MATCH'])) {
            self::$requestCacheETag = trim($_SERVER['HTTP_IF_NONE_MATCH']);
        }

        $offset = 3 /*month*/ * 30 /*days*/ * 24 /*hour*/ * 60 /*min*/ * 60 /*sec*/;
        if (self::$requestCacheTime != null && self::$requestCacheTime < $offset + time() && STAGE == PRODUCTION_STAGE) {
            header('HTTP/1.1 304 Not Modified');
            exit;
        }

        if (ENABLE_CSRF_PROTECTION) {
         //   self::enableCsrfProtection();
        }
    }

    /**
     * Генерує Csrf токен для захисту від міжсайтових запитів
     *
     * @return string
     */
    public static function generateCsrfToken($value = '')
    {
        $key = Session::Singleton()->getSessionId();
        $key .= $value;
        $key .= CMS_Bazalt::getSecretKey();

        return md5($key);
    }

    public static function getCsrfTokenName($id = '')
    {
        return md5(self::CSRF_TOKEN_FIELD . $id);
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

    public static function getBrowserCacheTime()
    {
        return strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
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
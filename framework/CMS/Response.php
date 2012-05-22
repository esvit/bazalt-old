<?php

class CMS_Response extends Object implements ISingleton
{
    const BAZALT_EXPOSE_HEADER = 'Bazalt CMS (http://bazalt-cms.com/)';

    /**
     * Default protocol to use if it cannot be detected
     */
    const DEFAULT_PROTOCOL = 'HTTP';

    /**
     * Default protocol version to use if cannot be detected
     */
    const DEFAULT_PROTOCOL_VERSION = '1.1';

    protected static $headers = array();

    protected static $respCode = 200;

    public static $messages = array(
        // Informational 1xx
        100 => 'Continue',
        101 => 'Switching Protocols',

        // Success 2xx
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',

        // Redirection 3xx
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found', // 1.1
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        // 306 is deprecated but reserved
        307 => 'Temporary Redirect',

        // Client Error 4xx
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',

        // Server Error 5xx
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        509 => 'Bandwidth Limit Exceeded'
    );

    public function __construct()
    {
        parent::__construct();

        // turn off implicit flushing
        ob_implicit_flush(0);

        header('Vary: Accept-Encoding');
    }

    public static function pageNotFound()
    {
        self::$respCode = 404;
    }
    
    public static function accessDenied()
    {
        self::$respCode = 403;
    }

    public static function noCache()
    {
        self::$headers['Expires'] = 'Sat, 26 Jul 1997 05:00:00 GMT';                # Date in the past   
        self::$headers['Last-Modified'] = gmdate('D, d M Y H:i:s') . ' GMT';
        self::$headers['Cache-Control'] = 'no-store, no-cache, must-revalidate, pre-check=0, post-check=0, max-age=0';  # HTTP/1.1
        self::$headers['Pragma'] = 'no-cache';
    }

    public static function cacheTime($sec, $min = 1, $hour = 1, $days = 1, $month = 1)
    {
        $offset = $month /*month*/ * $days /*days*/ * $hour /*hour*/ * $min /*min*/ * $sec /*sec*/;
        self::$headers['Expires'] = gmdate('D, d M Y H:i:s', time() + $offset) . ' GMT';

        if (CMS_Request::isIE()) {
           self::$headers['Cache-Control'] = 'private';
        } else {
           self::$headers['Cache-Control'] = 'max-age=' . $offset;
        }
    }

    public static function hasCache($content)
    {
        $cacheTime = CMS_Request::getCacheTime();
        $etag = md5($content . Session::Singleton()->getSessionId());

        // якщо вміст змінився
        if (!$cacheTime || CMS_Request::getCacheETag() != $etag) {
            $cacheTime = time();
        } else {
            self::cacheTime(60 /*sec*/, 60 /*min*/);
        }
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $cacheTime) . ' GMT');
        header('Etag: ' . $etag);

        if (CMS_Request::getCacheETag() == $etag) { 
            header('HTTP/1.1 304 Not Modified');
            return true;
        }
        return false;
    }

    public static function output($content)
    {
        self::$headers['Content-Type'] = 'text/html; charset=utf-8';

        self::sendHeaders();
        if (!self::hasCache($content)) {
            switch (self::$respCode) {
            case 404:
                header('HTTP/1.0 404 Not Found');
                break;
            case 403:
                header('HTTP/1.0 403 Forbidden');
                break;
            default:
                header('HTTP/1.0 200 OK');//$_SERVER["SERVER_PROTOCOL"]
                break;
            }
            echo $content;
        } else {
            exit;
        }
    }

    public static function notFound()
    {
        header('HTTP/1.0 404 Not Found');
        exit;
    }

    public static function setHeader($name, $value)
    {
        self::$headers[$name] = $value;
    }

    public static function sendHeaders()
    {
        if (!headers_sent()) {
            $protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : self::DEFAULT_PROTOCOL . '/' . self::DEFAULT_PROTOCOL_VERSION;

            if (!defined('BAZALT_EXPOSE') || BAZALT_EXPOSE) {
                self::$headers['X-Powered-By'] = self::BAZALT_EXPOSE_HEADER;
            }

            // status
            header($protocol . ' ' . self::$respCode . ' ' . self::$messages[ self::$respCode ]);

            foreach (self::$headers as $name => $value) {
                if (is_string($name)) {
                    // Combine the name and value to make a raw header
                    $value = $name . ': ' . $value;
                }
                // Send the raw header
                header($value, TRUE);
            }
        }
    }
}

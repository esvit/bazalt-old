<?php
/**
 * DataType_Url
 *
 * @category   Core
 * @package    BAZALT
 * @subpackage DataType
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    SVN: $Revision: 178 $
 * @link       http://bazalt-cms.com/
 */
 
define('URL_CHAR_REFS_REGEX', '/&([A-Za-z0-9\x80-\xff]+);|&\#([0-9]+);|&\#x([0-9A-Za-z]+);|&\#X([0-9A-Za-z]+);|(&)/x' );

define('URL_UTF8_REPLACEMENT', "\xEF\xBF\xBD");

define('URL_DEFAULT_ALPHABET', '123456789abcdefghijkmnopqrstuvwxyz');

/**
 * DataType_Url
 *
 * @category   Core
 * @package    BAZALT
 * @subpackage DataType
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */
class DataType_Url extends DataType_String
{
    const PUNYCODE_PREFIX = 'xn--';

    /**
     * e.g. http
     */
    protected $scheme;

    /**
     * host
     */
    protected $host;

    /**
     * host
     */
    protected $port;

    /**
     * host
     */
    protected $user;

    /**
     * host
     */
    protected $pass;

    /**
     * host
     */
    protected $path;

    /**
     * after the hashmark #
     */
    protected $fragment;

    protected $isDirty = false;

    protected $responseCode = 0;

    protected $headers = array();

    /**
     * after the question mark ?
     */
    protected $params = array();

    public function __construct($url)
    {
        $urlComponents = parse_url($url);

        $this->scheme = (isset($urlComponents['scheme'])) ? $urlComponents['scheme'] : null;
        $this->host = (isset($urlComponents['host'])) ? $urlComponents['host'] : null;
        $this->port = (isset($urlComponents['port'])) ? $urlComponents['port'] : null;
        $this->user = (isset($urlComponents['user'])) ? $urlComponents['user'] : null;
        $this->pass = (isset($urlComponents['pass'])) ? $urlComponents['pass'] : null;
        $this->path = (isset($urlComponents['path'])) ? $urlComponents['path'] : null;
        $this->fragment = (isset($urlComponents['fragment'])) ? $urlComponents['fragment'] : null;

        $params = array();
        if (isset($urlComponents['query'])) {
            parse_str($urlComponents['query'], $params);
        }

        $this->setParams($params);
        $this->isDirty = false;

        parent::__construct($url);
    }

    public function getResponseCode()
    {
        return $this->responseCode;
    }

    public function setHeaders($headers = array())
    {
        $this->headers = $headers;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function path($path = null)
    {
        if ($path != null) {
            $this->path = $path;
            $this->isDirty = true;
            return $this;
        }
        return $this->path;
    }

    public function setParams($value = array())
    {
        if (!is_array($value)) {
            throw new Exception('Invalid argument');
        }
        $this->isDirty = true;
        $this->params = array();
        foreach ($value as $k => $item) {
            $this->params[$k] = urlencode($item);
        }
    }

    public static function isValid($string, $protocols = array('http', 'https'))
    {
        $protocols = implode('|', $protocols);
        return preg_match('/^(' . $protocols . '):\/\/([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i', $string) != 0;
    }

    public static function goBack()
    {
        self::redirect(self::getReferer());
    }

    public static function getReferer()
    {
        return $_SERVER['HTTP_REFERER'];
    }

    public static function getRequestUrl($withScriptName = false, $withHost = false)
    {
        $url = '';
        if (array_key_exists('HTTP_X_ORIGINAL_URL', $_SERVER)) {
            // IIS specific
            $url = $_SERVER['HTTP_X_ORIGINAL_URL'];
        } else if (!$withScriptName && array_key_exists('PATH_INFO', $_SERVER)) {
            // For url like /index.php/etc/...
            $url = $_SERVER['PATH_INFO'];
        } else {
            $url = $_SERVER['REQUEST_URI'];
        }
        $urlData = parse_url($url);
        $url = isset($urlData['path']) ? $urlData['path'] : '/';
        if ($withHost) {
            $url = self::getHostname() . $url;
        }
        return $url;
    }

    /**
     * Get user remote ip address
     */
    public static function getRemoteIp()
    {
        $ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ?
                (isset($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['HTTP_X_REAL_IP'] : $_SERVER['HTTP_X_FORWARDED_FOR']) :
                $_SERVER['REMOTE_ADDR'];

        return $ip;
    }
 
    public static function isSecure()
    {
        $https = isset($serv['HTTPS']) ? $serv['HTTPS'] : 'off';
        return ($https == '1' || $https == 'on');
    }

    /**
     * Return current hostname: server name with protocol
     *
     * @return string like "http://bazalt-cms.com" or "https://bazalt-cms.com:8080"
     */
    public static function getHostname()
    {
        $https = isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : 'off';

        $serverName  = self::isSecure() ? 'https' : 'http';
        $serverName .= '://';
        $serverName .= self::getDomain();

        return $serverName;
    }

    public static function needPortInDomain()
    {
        if (!isset($_SERVER['SERVER_PORT'])) {
            return false;
        }
        return !($_SERVER['SERVER_PORT'] == 80 || $_SERVER['SERVER_PORT'] == 443);
    }

    public static function getCookieDomain()
    {
        if (!isset($_SERVER['SERVER_NAME'])) {
            // when in cli mode
            return null;
        }
        $serverName = $_SERVER['SERVER_NAME'];

        // If server name has port number attached then strip it
        $colon = strpos($serverName, ':');
        if ($colon) {
            $serverName = substr($serverName, 0, $colon);
        }
        return $serverName;
    }

    /**
     * Return current server name
     *
     * @return string like "bazalt-cms.com" or "bazalt-cms.com:8080"
     */
    public static function getDomain()
    {
        if (!isset($_SERVER['SERVER_NAME'])) {
            // when in cli mode
            return null;
        }
        $serverName = $_SERVER['SERVER_NAME'];

        if (substr($serverName, 0, 4) == self::PUNYCODE_PREFIX) {
            $convertor = new Core_IDNConvertor(array('idn_version' => 2008));
            $serverName = $convertor->decode($serverName);
        }
        // If server name has port number attached then strip it
        $colon = strpos($serverName, ':');
        if ($colon) {
            $serverName = substr($serverName, 0, $colon);
        }
        $serverName .= !self::needPortInDomain() ? '' : ':' . $_SERVER['SERVER_PORT'];

        return $serverName;
    }

    /**
     * Redirect to url
     *
     * @return void
     */
    public static function redirect($url)
    {
        $url = is_object($url) ? $url->__toString() : $url;
        /*if (!self::isValid($url)) {
            throw new Exception('Invalid argument');
        }*/
        header('Location: ' . $url);
        exit;
    }

    /**
     * Download content by url
     *
     * @return string
     */
    public static function getContent($url)
    {
        $url = new DataType_Url($url);

        return $url->get();
    }

    public function post($post = array())
    {
        $curl = $this->createCurl($this->toString());

        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));

        $response = curl_exec($curl);
        $this->responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        return $response;
    }

    protected function createCurl($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 25);
        curl_setopt($curl, CURLOPT_TIMEOUT, 25);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 5);

        if (is_array($this->headers) && count($this->headers) > 0) {
            $stringHeaders = array();
            foreach ($this->headers as $key => $value) {
                $stringHeaders []= "$key: $value";
            }
            curl_setopt($curl, CURLOPT_HTTPHEADER, $stringHeaders);
        }
        return $curl;
    }

    public function get()
    {
        $curl = $this->createCurl($this->toString());

        curl_setopt($curl, CURLOPT_HEADER, false);

        $content = curl_exec($curl);
        $this->responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);
        return $content;
    }

    public function toString()
    {
        if (!$this->isDirty) {
            return $this->value;
        }
        $url = '';

        if (isset($this->scheme)) {
            $url .= $this->scheme . '://';
        }
        if (isset($this->user)) {
            $url .= $this->user;
            if (isset($this->pass)) {
                $url .= ':' . $this->pass;
            }
            $url .= '@';
        }
        if (isset($this->host)) {
            $url .= $this->host;
        }
        if (isset($this->port)) {
            $url .= ':' . $this->port;
        }
        if (isset($this->path)) {
            $url .= $this->path;
        }

        $params = urldecode(http_build_query($this->params));
        if (!empty($params)) {
            $url .= '?' . $params;
        }
        if (isset($this->fragment)) {
            $url .= '#' . $this->fragment;
        }
        return $url;
    }

    protected static function validateCodepoint($codepoint)
    {
        return ($codepoint ==    0x09)
            || ($codepoint ==    0x0a)
            || ($codepoint ==    0x0d)
            || ($codepoint >=    0x20 && $codepoint <=   0xd7ff)
            || ($codepoint >=  0xe000 && $codepoint <=   0xfffd)
            || ($codepoint >= 0x10000 && $codepoint <= 0x10ffff);
    }

    protected static function decodeChar($codepoint)
    {
        if (self::validateCodepoint($codepoint)) {
            return chr($codepoint);
        } else {
            return URL_UTF8_REPLACEMENT;
        }
    }

    protected static function decodeCharReferences( $text )
    {
        return preg_replace_callback(URL_CHAR_REFS_REGEX, array(__CLASS__, 'decodeCharReferencesCallback'), $text);
    }

    /**
     * @param $matches String
     * @return String
     */
    protected static function decodeCharReferencesCallback($matches)
    {
        if ($matches[1] != '') {
            return html_entity_decode('&' . $matches[1] . ';', ENT_QUOTES, 'UTF-8');
        } elseif ($matches[2] != '') {
            return self::decodeChar(intval($matches[2]));
        } elseif ($matches[3] != '') {
            return self::decodeChar(hexdec($matches[3]));
        } elseif ($matches[4] != '') {
            return self::decodeChar(hexdec($matches[4]));
        }
        # Last case should be an ampersand by itself
        return '&';
    }

    public static function cleanUrl($url, $replace = array(), $delimiter = '-')
    {
        $url = self::decodeCharReferences($url);

        if (!empty($replace)) {
            $url = str_replace((array)$replace, ' ', $url);
        }

        $replaceSymbols = array(
            '«', '»', '”', '“', "\xC2\xA0" /* no break space */
        );

        // remove symbols
        $url = preg_replace('/[\\x00-\\x19\\x21-\\x2F\\x3A-\\x40\\x5B-\\x60\\x7B-\\x7F]/', ' ', $url);

        $url = str_replace($replaceSymbols, ' ', $url);

        $url = preg_replace("/\s+/", ' ', $url);

        $url = preg_replace("/[\/_|+ -]+/", $delimiter, $url);

        $url = mb_strToLower(trim($url, $delimiter));

        return $url;
    }

    public static function encodeId($num, $alphabet = URL_DEFAULT_ALPHABET)
    {
        $baseCount = strlen($alphabet);
        $encoded = '';
        while ($num >= $baseCount) {
            $div = $num / $baseCount;
            $mod = ($num - ($baseCount*intval($div)));
            $encoded = $alphabet[$mod] . $encoded;
            $num = intval($div);
        }

        if ($num) {
            $encoded = $alphabet[$num] . $encoded;
        }
        return $encoded;
    }

    public static function decodeId($num, $alphabet = URL_DEFAULT_ALPHABET)
    {
        $decoded = 0;
        $multi = 1;
        while (strlen($num) > 0) {
            $digit = $num[strlen($num)-1];
            $decoded += $multi * strpos($alphabet, $digit);
            $multi = $multi * strlen($alphabet);
            $num = substr($num, 0, -1);
        }
        return $decoded;
    }
}
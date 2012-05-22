<?php

class CMS_Browser
{
    public static function headerNoCache()
    {
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');               # Date in the past   
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: no-store, no-cache, must-revalidate');   # HTTP/1.1
        header('Cache-Control: pre-check=0, post-check=0, max-age=0');  # HTTP/1.1
        header('Pragma: no-cache');
    }

    public static function isOldIE()
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        return ((stripos($userAgent, 'MSIE 5.5') !== FALSE || stripos($userAgent, 'MSIE 6.0') !== FALSE)
          && stripos($userAgent, 'MSIE 8.0') === FALSE && stripos($userAgent, 'MSIE 7.0') === FALSE);
    }

    public static function isIE()
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        return (isset($userAgent) && (strpos($userAgent, 'MSIE') !== false));
    }
}
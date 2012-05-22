<?php

/**
 * Google PageRank Fetcher
 *
 * Requires an API key to access Technorati stats. 
 * (Get one at http://technorati.com/developers/apikey.html)
 *
 * @author Mark Woodman, http://techbrew.net 
 *         Based on original code presumed to be in the public domain.
 *
 * $URL: http://popstats.googlecode.com/svn/trunk/google_pagerank.class.php $
 * $Rev: 139 $
 * $Author: mark.a.woodman $
 * $Date: 2007-08-17 18:59:37 +0300 (Пт, 17 Сер 2007) $
 * $Id: googlepagerank.class.inc 139 2010-11-14 19:25:26Z esvit $
 */
class GooglePageRank 
{  
    /**
     * Constructor.
     *
     * @param url        The site URL to check for PageRank
     * @param cacheTime  (Optional) Length of time in seconds to cache results.
     */
    public static function getPageRank($url) 
    {
        $uri = 'http://toolbarqueries.google.com/search?client=navclient-auto&ch=' . self::checkHash($url) . '&features=Rank&q=info:' . $url . '&num=100&filter=0';
        //$fp = fsockopen('toolbarqueries.google.com', 80, $errno, $errstr, 30);
        
        $content = Url::getContent($uri); // считываем файл
                $pos = strpos($content, 'Rank_');
                if ($pos !== false) {
                    $pagerank = substr($content, $pos + 9);
                    return intval($pagerank);
                }
        /*if (!$fp) {
            return 0;
        } else {
            $out  = 'GET /search?client=navclient-auto&ch=' . self::checkHash($url) . '&features=Rank&q=info:' . $url . '&num=100&filter=0 HTTP/1.1' . "\r\n";
            $out .= 'Host: toolbarqueries.google.com' . "\r\n";
            $out .= 'User-Agent: Mozilla/4.0 (compatible; GoogleToolbar 2.0.114-big; Windows XP 5.1)' . "\r\n";
            $out .= 'Connection: Close' . "\r\n\r\n";

            fwrite($fp, $out);

            while (!feof($fp)) {
                $data = fgets($fp, 128);
                $pos = strpos($data, 'Rank_');
                if ($pos !== false) {
                    $pagerank = substr($data, $pos + 9);
                    return intval($pagerank);
                }
            }
            fclose($fp);
        }*/
    }

    /**
     * convert a string to a 32-bit integer
     */
    protected static function strToNum($Str, $Check, $Magic)
    {
        $Int32Unit = 4294967296;  // 2^32

        $length = strLen($Str);
        for ($i = 0; $i < $length; $i++) {
            $Check *= $Magic;
            //If the float is beyond the boundaries of integer (usually +/- 2.15e+9 = 2^31), 
            //  the result of converting to integer is undefined
            //  refer to http://www.php.net/manual/en/language.types.integer.php
            if ($Check >= $Int32Unit) {
                $Check = ($Check - $Int32Unit * (int) ($Check / $Int32Unit));
                //if the check less than -2^31
                $Check = ($Check < -2147483648) ? ($Check + $Int32Unit) : $Check;
            }
            $Check += ord($Str{$i});
        }
        return $Check;
    }

    /**
     * Genearate a hash for a url
     */
    protected static function hashURL($string)
    {
        $check1 = self::strToNum($string, 0x1505, 0x21);
        $check2 = self::strToNum($string, 0, 0x1003F);

        $check1 >>= 2; 	
        $check1 = (($check1 >> 4) & 0x3FFFFC0 ) | ($check1 & 0x3F);
        $check1 = (($check1 >> 4) & 0x3FFC00 ) | ($check1 & 0x3FF);
        $check1 = (($check1 >> 4) & 0x3C000 ) | ($check1 & 0x3FFF);

        $t1 = (((($check1 & 0x3C0)      << 4) | ($check1 & 0x3C))   << 2)   | ($check2 & 0xF0F);
        $t2 = (((($check1 & 0xFFFFC000) << 4) | ($check1 & 0x3C00)) << 0xA) | ($check2 & 0xF0F0000);

        return ($t1 | $t2);
    }

    /**
     * genearate a checksum for the hash string
     */
    protected static function checkHash($url)
    {
        $CheckByte = 0;
        $Flag = 0;

        $hashStr = sprintf('%u', self::hashURL($url));
        $length = strlen($hashStr);

        for ($i = $length - 1;  $i >= 0;  $i --) {
            $Re = $hashStr{$i};

            if (1 === ($Flag % 2)) {              
                $Re += $Re;     
                $Re = (int)($Re / 10) + ($Re % 10);
            }

            $CheckByte += $Re;
            $Flag ++;
        }

        $CheckByte %= 10;

        if (0 !== $CheckByte) {
            $CheckByte = 10 - $CheckByte;

            if (1 === ($Flag % 2) ) {
                if (1 === ($CheckByte % 2)) {
                    $CheckByte += 9;
                }
                $CheckByte >>= 1;
            }
        }

        return '7' . $CheckByte . $hashStr;
    }

}
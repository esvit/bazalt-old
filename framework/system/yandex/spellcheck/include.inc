<?php

class YandexSpellcheck
{
    public static function detectLanguage($string)
    {
        $url = 'http://ajax.googleapis.com/ajax/services/language/detect?v=1.0&q=' . urlencode($string);

        $query = curl_init($url);

        curl_setopt($query, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($query, CURLOPT_HEADER, false);
        curl_setopt($query, CURLOPT_SSL_VERIFYPEER, false);

        $reqResult = curl_exec($query);
        $content = json_decode($reqResult);
        if ($content->responseStatus == 200) {
            return $content->responseData->language;
        }
        return null;
    }

    public static function checkString($string, $lang = 'uk')
    {
        $url = 'http://speller.yandex.net/services/spellservice/checkText?lang=' . $lang . '&text=' . urlencode($string);
        $content = Url::getContent($url);
        $ret = simplexml_load_string($content);
        return $ret;
    }

    public static function correctString($string)
    {
        $cacheKey = __CLASS__ . __FUNCTION__ . $string;
        $cached = Cache::Singleton()->getCache($cacheKey);
        if ($cached !== false) {
            return $cached;
        }

        $lang = self::detectLanguage($string);
        $ret = self::checkString($string, $lang);

        $correctString = '';
        $prevPos = 0;
        //$ret->error
        foreach ($ret->error as $err) {
            $attrs = (array)$err->attributes();
            $attrs = $attrs['@attributes'];

            $correctString .= mb_substr($string, $prevPos, $attrs['pos'] - $prevPos);

            $errWord = (string)$err->word;
            $correctWord = (string)$err->s;
            if (!empty($correctWord)) {
                $correctString .= '<em>' . $correctWord . '</em>';
            } else {
                $correctString .= mb_substr($string, $attrs['pos'], $attrs['len']);
            }
            $prevPos = $attrs['pos'] + $attrs['len'];
        }
        $correctString .= mb_substr($string, $prevPos);

        Cache::Singleton()->setCache($cacheKey, $correctString, Cache::DEFAULT_LIFE_TIME);
        return $correctString;
    }
}
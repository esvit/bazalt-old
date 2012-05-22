<?php

class Locale_Detector_Browser extends Locale_Detector_Abstract implements ISingleton
{
    public function detectLocale()
    {
        if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            return null;
        }
        $acceptLanguage = array();
        foreach (explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']) as $lang) {
            // Cut off any q-value that might come after a semi-colon
            if ($pos = strpos($lang, ';')) {
                $lang = trim(substr($lang, 0, $pos));
            }
            if (strstr($lang, '-')) {
                list($pri, $sub) = explode('-', $lang);
                if ($pri == 'i') {
                    /**
                    * Language not listed in ISO 639 that are not variants
                    * of any listed language, which can be registerd with the
                    * i-prefix, such as i-cherokee
                    */
                    $lang = $sub;
                } else {
                    $lang = $pri;
                }
            }
            $acceptLanguage[] = $lang;
        }
        return array_unique($acceptLanguage);
    }
}
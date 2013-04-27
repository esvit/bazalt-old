<?php

namespace Framework\System\Locale;

abstract class AbstractLanguage
{
    /**
     * Називний (Хто? Що?) (весна, явір)
     */
    const NOMINATIVE_CASE = 0;

    /**
     * Родовий (Кого? Чого?) (весни, явора)
     */
    const GENITIVE_CASE = 1;

    /**
     * Давальний (Кому? Чому?) (весні, явору)
     */
    const DATIVE_CASE = 2;

    /**
     * Знахідний (Кого? Що?) (весну,явір)
     */
    const ACCUSATIVE_CASE = 3;

    /**
     * Орудний (Ким? Чим?) (весною, явором)
     */
    const INSTRUMENTAL_CASE = 4;

    /**
     * Місцевий (Про кого? Про що?) (на весні, на яворі)
     */
    const PREPOSITIONAL_CASE = 5;

    /**
     * @see http://www.gnu.org/s/hello/manual/gettext/Usual-Language-Codes.html#Usual-Language-Codes
     */
    abstract function getLanguageName();

    abstract function getCountries();

    abstract function getDateFormats();

    abstract function getCurrencies();

    abstract function getLanguages();

    abstract function getTimeFormats();

    abstract function getRegions();

    public function getPluralCount()
    {
        return 2;
    }

    /**
     * Приведення слів до відповідного відмінку
     */
    public function toCase($word, $choice)
    {
        return $word;
    }

    public function getPluralExpresion()
    {
        return '(n != 1) ? 0 : 1';
    }
    
    public function getPluralFormsNumber()
    {
        $forms = array();
        $func = self::MakePluralFunction($this->getPluralCount(), $this->getPluralExpresion());
        
        $this->getPluralExpresion();
        if (!$func) {
            return array();
        }
        for ($i = 1; $i < 100; $i++) {
            $index = $func($i);
            if (!array_key_exists($index, $forms)) {
                $forms[$index] = $i;
            }
            if (count($forms) == $this->getPluralCount()) {
                break;
            }
        }
        ksort($forms);
        return $forms;
    }
    
    /**
     * Makes a function, which will return the right translation index, according to the
     * plural forms header
     */
    public static function MakePluralFunction($nplurals, $expression)
    {
        $expression = str_replace('n', '$n', $expression);
        $funcBody = "\$index = (int)($expression); return (\$index < $nplurals)? \$index : $nplurals - 1;";
        return create_function('$n', $funcBody);
    }

    public function getCountryByCode($code)
    {
        $countries = $this->getCountries();
        $code = strToUpper($code);
        if (!array_key_exists($code, $countries)) {
            return null;
        }
        return $countries[$code];
    }

    public function getCurrencyByCode($code)
    {
        $currency = $this->getCurrencies();
        $code = strToUpper($code);
        if (!array_key_exists($code, $currency)) {
            return null;
        }
        return $currency[$code];
    }

    public function getLanguageByCode($code)
    {
        $languages = $this->getLanguages();
        $code = strToLower($code);
        if (!array_key_exists($code, $languages)) {
            return null;
        }
        return $languages[$code];
    }

    public function getRegionName($code)
    {
        $regions = $this->getRegions();
        $code = strToLower($code);
        if (!array_key_exists($code, $regions)) {
            return null;
        }
        return $regions[$code];
    }

    public function translit($string)
    {
        return $string;
    }
}
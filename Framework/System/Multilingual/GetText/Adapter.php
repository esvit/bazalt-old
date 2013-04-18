<?php

namespace Framework\System\Multilingual\GetText;

use Framework\System\Multilingual as Multilingual;

class Adapter extends Multilingual\TranslateAdapter
{
    const DEFAULT_DOMAIN = 'BAZALT';

    protected $defaultDomain = self::DEFAULT_DOMAIN;

    protected $files = array();

    protected $domains = array();
    
    protected $language;

    public function language($language = null)
    {
        if ($language != null) {
            $this->language = $language;
            return $this;
        }
        return $this->language;
    }

    public function translate($string, $pluralString = null, $count = 0)
    {
        if (empty($pluralString)) {
            $pluralString = $string;
        }
        //textdomain($domain);
        //bind_textdomain_codeset($domain, 'UTF-8');

        $file = self::getLocaleFile($this->scope->language(), $this->scope->domain());

        //return gettext($string);
        if ($file != null) {
            $transl = $file->translate($string, $count);
            if (!empty($transl)) {
                return $transl;
            }
        }
        if ($count > 0) {
            $en = Locale_Config::findLocaleByAlias(Multilingual\Domain::DEFAULT_LANGUAGE);
            $func = Locale_AbstractLanguage::MakePluralFunction($en->getPluralCount(), $en->getPluralExpresion());
            if ($func($count) == 0) {
                return $pluralString;
            }
        }
        return $string;
    }

    protected function getLocaleFile($locale, $domain = null)
    {
        $domain = ($domain == null) ? $this->defaultDomain : $domain;

        $file = sprintf('%s/%s/%s.po', $this->scope->localeFolder(), $locale, $domain);

        if (!array_key_exists($file, $this->files) && file_exists($file)) {
            $this->files[$file] = SimplePoReader::importFromFile($file);
        }
        if (array_key_exists($file, $this->files)) {
            return $this->files[$file];
        }
        return null;
    }
}
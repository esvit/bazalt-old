<?php

class Locale_GetText_Adapter extends Locale_Translation_AbstractAdapter
{
    const DEFAULT_DOMAIN = 'BAZALT';

    protected $defaultDomain = null;

    protected $files = array();

    protected $domains = array();
    
    protected $locale;

    public function initLocale(Locale $locale, $domain = null)
    {
        $this->locale = $locale;
        $this->defaultDomain = ($domain == null) ? self::DEFAULT_DOMAIN : $domain;
    }

    public function getTranslation($string, $domain = null)
    {
        return $this->getPluralTranslation($string, $string, 0, $domain);
    }

    public function getPluralTranslation($string, $pluralString, $n = 0, $domain = null)
    {
        //textdomain($domain);
        //bind_textdomain_codeset($domain, 'UTF-8');

        $file = self::getLocaleFile(Locale::getLanguage(), $domain);

        //return gettext($string);
        if ($file != null) {
            $transl = $file->translate($string, $n);
            if (!empty($transl)) {
                return $transl;
            }
        }
        return $string;
    }

    protected function getLocaleFile($locale, $domain = null)
    {
        $domain = ($domain == null) ? $this->defaultDomain : $domain;

        if (!array_key_exists($domain, $this->domains)) {
            return null;
        }
        $file = $this->domains[$domain];
        $file .= '/' . $locale . '/' . $domain . '.po';

        if (!array_key_exists($file, $this->files) && file_exists($file)) {
            $this->files[$file] = Locale_GetText_PoReader::importFromFile($file);
        }
        if (array_key_exists($file, $this->files)) {
            return $this->files[$file];
        }
        return null;
    }

    public function bindTextDomain($file, $domain = null)
    {
        $domain = ($domain == null) ? $this->defaultDomain : $domain;
        /*if (STAGE != PRODUCTION_STAGE) {
            bindtextdomain($domain, $file);
        } else {*/
            $this->domains[$domain] = $file;
        //}
    }

    public function readDictionary($name, $folder, $locale = null)
    {
        if ($locale == null) {
            $locale = Locale::getLanguage();
        }
        $name = strToLower($name);
        $filename = $folder . PATH_SEP . $name . '.pot';

        // read templates strings
        try {
            $templateFile = Locale_GetText_PoReader::importFromFile($filename);
        } catch (Exception $ex) {
            return null;
        }
        $templateStrings = $templateFile->getEntries();

        $filename = $folder . PATH_SEP . $locale . PATH_SEP . $name . '.po';

        // read translated strings
        try {
            $file = Locale_GetText_PoReader::importFromFile($filename);
            $strings = $file->getEntries();
        } catch (Exception $ex) {
        }

        $dictionary = new Locale_Translation_Dictionary($name, $folder, $this);
        $dictionary->templatesTime($templateFile->getFileTime());

        if ($file) {
            $dictionary->setPluralCount($file->getPluralCount());
            $dictionary->setPluralFormsNumber($file->getPluralFormsNumber());
        } else {
            $localeObj = Locale::findLocaleByAlias($locale);
            $dictionary->setPluralCount($localeObj->getPluralCount());
            $dictionary->setPluralFormsNumber($localeObj->getPluralFormsNumber());
        }
        
        // merge templates and translated strings
        foreach ($templateStrings as $string) {
            $origStr = $string->getString();
            if (array_key_exists($origStr, $strings)) {
                $string = $strings[$origStr];
            }
            $dictionary->addEntry($string);
        }

        return $dictionary;
    }

    public function saveDictionary(Locale_Translation_Dictionary $dict, $folder = null, $locale = null)
    {
        $name = strToLower($dict->getName());
        if ($folder == null) {
            $folder = $dict->getFolder();
        }
        if ($locale == null) {
            $path = $folder;
            $fileName = $path . PATH_SEP . $name . '.pot';
        } else {
            $path = $folder . PATH_SEP . $locale;
            $fileName = $path . PATH_SEP . $name . '.po';
        }

        mkdir($path, 0777);

        if ($locale == null) {
            $language = Locale::findLocaleByAlias('en');
        } else {
            $language = Locale::findLocaleByAlias($locale);
        }

        $file = new Locale_GetText_PoReader();
        $file->setEntries($dict->getEntries());
        $file->setHeaders(
            array(
                'Project-Id-Version' => 'BAZALT CMS',
                'Report-Msgid-Bugs-To' => 'support@equalteam.net',
                'POT-Creation-Date' => date('Y-m-d H:iO', (($locale == null) ? time() : $dict->templatesTime())),
                'PO-Revision-Date' => date('Y-m-d H:iO'),
                'Last-Translator' => 'BAZALT CMS <support@equalteam.net>',
                'Language-Team' => $language->getLanguageName(),
                'MIME-Version' => '1.0',
                'Content-Type' => 'text/plain; charset=UTF-8',
                'Content-Transfer-Encoding' => '8bit',
                'Plural-Forms' => 'nplurals=' . $language->getPluralCount() . '; plural=' . $language->getPluralExpresion() . ';'
            )
        );
        $file->exportToFile($fileName);
        Cache::Singleton()->removeByTag('po_file');
    }
}
<?php

namespace Framework\CMS\tests\Model;

use Framework\CMS\Model\Language,
    Framework\CMS\Model\Site;
use Framework\System\ORM\ORM;

class LanguageTest extends \Tests\BaseCase
{
    /**
     * @var Language
     */
    protected $language = null;
    /**
     * @var Language
     */
    protected $site = null;

    protected function setUp()
    {
        $q = ORM::delete('Framework\CMS\Model\Language');
        $q->exec();

        $this->site = Site::create();
        $this->site->save();

        $this->language = Language::create('New lang', 'nl', 'nl');
        $this->language->save();

        $this->site->DefaultLanguage = $this->language;
    }

    protected function tearDown()
    {
        if ($this->site)
            $this->site->delete();

        if ($this->language)
            $this->language->delete();
    }

    public function testGetSiteLanguages()
    {
        $langs = Language::getSiteLanguages(true, $this->site);
        $titles = [];
        foreach ($langs as $lang) {
            $titles []= $lang->alias;
        }

        $this->assertEquals([
            'nl'
        ], $titles);

        $language = Language::create('UK', 'ukr', 'ukr');
        $language->save();

        $this->site->addLanguage($language);

        $langs = Language::getSiteLanguages(true, $this->site);
        $titles = [];
        foreach ($langs as $lang) {
            $titles []= $lang->alias;
        }

        // non multilanguage
        $this->assertEquals([
            'nl'
        ], $titles);

        $this->site->is_multilingual = true;
        $langs = Language::getSiteLanguages(true, $this->site);
        $titles = [];
        foreach ($langs as $lang) {
            $titles []= $lang->alias;
        }

        // multilanguage
        $this->assertEquals([
            'nl',
            'ukr'
        ], $titles);
    }
}
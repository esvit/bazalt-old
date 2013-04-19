<?php

namespace Framework\System\Multilingual\tests;

use Framework\System\Multilingual as Multilingual;

class TranslationTest extends \Tests\BaseCase
{
    /**
     * @var Multilingual\Domain
     */
    protected $tr = null;

    protected function setUp()
    {
        $this->tr = Multilingual\Domain::newDomain(
            'test',
            [ dirname(__FILE__) . '/templates' ],
            dirname(__FILE__) . '/locale'
        );
        Multilingual\Domain::root()->language('en');
    }

    protected function tearDown()
    {
        Multilingual\Domain::clearDomains();
        $this->tr = null;
    }

    public function testFileLoading()
    {
        Multilingual\Domain::root()->language('uk');

        $str = $this->tr->translate('Test');

        $this->expectOutputString(
            "Set expression nplurals=3; plural=((n%10==1) && (n%100!=11)) ? 0 : (( (n%10>=2) && (n%10<=4) && (n%100<10 || n%100>=20)) ? 1 : 2 );\n" .
            "Load language file uk, test\n" .
            "Test => Ya, ya, good!\n" .
            "translate(Test => Ya, ya, good!)\n"
        );
    }

    public function testTranslation()
    {
        $str = $this->tr->translate('Test');

        $this->assertEquals("Test", $str);

        Multilingual\Domain::root()->language('uk');
        $str = $this->tr->translate('Test');

        $this->assertEquals("Ya, ya, good!", $str);

        $this->expectOutputString(
            "translate(Test => Test)\n" .
            "Set expression nplurals=3; plural=((n%10==1) && (n%100!=11)) ? 0 : (( (n%10>=2) && (n%10<=4) && (n%100<10 || n%100>=20)) ? 1 : 2 );\n" .
            "Load language file uk, test\n" .
            "Test => Ya, ya, good!\n" .
            "translate(Test => Ya, ya, good!)\n"
        );
    }

    public function testLocaleFolder()
    {
        Multilingual\Domain::root()->language('uk');

        $str = $this->tr->translate('Test');
        $this->assertEquals("Ya, ya, good!", $str);

        $folder = $this->tr->localeFolder();
        $this->tr->localeFolder($folder . '2');

        $str = $this->tr->translate('Test');
        $this->assertEquals("Ya, ya, good!\nFrom local2", $str);

        $this->tr->localeFolder($folder);

        $str = $this->tr->translate('%d review', '%d reviews', 0);
        $this->assertEquals("0 відгуків", $str);

        $this->assertEquals("1 відгук", $this->tr->translate('%d review', '%d reviews', 1));

        $this->assertEquals("2 відгука", $this->tr->translate('%d review', '%d reviews', 2));

        $this->assertEquals("3 відгука", $this->tr->translate('%d review', '%d reviews', 3));

        $this->assertEquals("5 відгуків", $this->tr->translate('%d review', '%d reviews', 5));
    }

    public function testNoExistTranslate()
    {
        $this->assertEquals("%d comment", $this->tr->translate('%d comment'));

        $this->assertEquals("%d comment", $this->tr->translate('%d comment', '%d comments', 1));

        $this->assertEquals("%d comments", $this->tr->translate('%d comment', '%d comments', 2));
    }

    public function testClearscopes()
    {
        $this->tr->clearDomains();
    }
}
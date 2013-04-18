<?php

use Framework\Core\Helper\Url;

class UrlTest extends Tests\BaseCase
{
    protected $url;

    public function setUp()
    {
        $this->url = new Url('http://username:password@hostname/path?arg=value#anchor');
    }

    public function tearDown()
    {
        unset($this->url);
    }

    public function testIsValid()
    {
        $this->assertFalse(Url::isValid('test'));

        $this->assertTrue(Url::isValid('http://test.ua/test?test=test#test'));
    }

    public function testGetReferer()
    {
        $_SERVER['HTTP_REFERER'] = 'test';

        $this->assertEquals(Url::getReferer(), 'test');
    }

    public function testGetRequestUrl()
    {
        $_SERVER['REQUEST_URI'] = '/test1';

        $this->assertEquals(Url::getRequestUrl(), '/test1');
        $_SERVER['PATH_INFO'] = '/test2';

        $this->assertEquals(Url::getRequestUrl(), '/test2');

        $_SERVER['HTTP_X_ORIGINAL_URL'] = '/test3'; //IIS
        $this->assertEquals(Url::getRequestUrl(), '/test3');
    }

    public function testCleanUrl()
    {
        $url = Url::cleanUrl('Mess\'d up --text-- just (to) stress /test/ ?our! `little` \\clean\\ url fun.ction!?-->');
        $this->assertEquals($url, 'mess-d-up-text-just-to-stress-test-our-little-clean-url-fun-ction');

        //$url = Url::cleanUrl("Perché l'erba è verde?", "'"); // Italian
        //$this->assertEquals($url, 'perche-l-erba-e-verde');

        //$url = Url::cleanUrl("Peux-tu m'aider s'il te plaît?", "'"); // French
        //$this->assertEquals($url, 'peux-tu-m-aider-s-il-te-plait');

        //$url = Url::cleanUrl("Tänk efter nu – förr'n vi föser dig bort"); // Swedish
        //$this->assertEquals($url, 'tank-efter-nu-forrn-vi-foser-dig-bort');

        //$url = Url::cleanUrl("ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖÙÚÛÜÝßàáâãäåæçèéêëìíîïðñòóôõöùúûüýÿ");
        //$this->assertEquals($url, 'aaaaaaaeceeeeiiiidnooooouuuuyssaaaaaaaeceeeeiiiidnooooouuuuyy');

        $url = Url::cleanUrl("Custom`delimiter*example", array('*', '`'));
        $this->assertEquals($url, 'custom-delimiter-example');

        $url = Url::cleanUrl("My+Last_Crazy|delimiter/example", '', ' ');
        $this->assertEquals($url, 'my last crazy delimiter example');
    }

    public function testEncodeId()
    {
        $id = Url::encodeId(100500);
        $this->assertEquals($id, '3jxw');
    }

    public function testDecodeId()
    {
        $id = Url::decodeId('3jxw');
        $this->assertEquals($id, 100500);
    }
}
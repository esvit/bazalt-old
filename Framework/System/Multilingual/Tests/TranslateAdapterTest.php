<?php

namespace Framework\System\Multilingual\tests;

use Framework\System\Multilingual as Multilingual;

class TranslateAdapterTest extends \Tests\BaseCase
{
    protected function setUp()
    {
    }

    protected function tearDown()
    {
    }

    public function testTranslate()
    {
        $root = Multilingual\Domain::root();
        $tr = new Multilingual\ArrayTranslate($root);

        $func = $tr->pluralExpression("nplurals=3; plural=((n%10==1) && (n%100!=11)) ? 0 : (( (n%10>=2) && (n%10<=4) && (n%100<10 || n%100>=20)) ? 1 : 2 );");
        $this->assertEquals(2, $func(0));
        $this->assertEquals(0, $func(1));
        $this->assertEquals(1, $func(2));
        $this->assertEquals(1, $func(3));
        $this->assertEquals(2, $func(5));
    }
}
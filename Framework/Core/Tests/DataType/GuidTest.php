<?php

use Framework\Core\Helper\Guid;

class GuidTest extends Tests\BaseCase
{
    public function testNewGuid()
    {
        $str = Guid::newGuid();
        $str2 = Guid::newGuid();
        $this->assertNotEquals($str, $str2);
    }

    public function testIsValid()
    {
        $this->assertTrue(Guid::isValid('00000000-0000-0000-0000-000000000000'));

        $this->assertFalse(Guid::isValid(''));
    }
}
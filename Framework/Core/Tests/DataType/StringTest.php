<?php

use Framework\Core\Helper\String;

class StringTest extends Tests\BaseCase
{
    public function testFormat()
    {
        $str = 'test {0} string {1}, {2}';
        $this->assertEquals(String::format($str, 'str1', 'str2', 'str3'), 'test str1 string str2, str3');

        $this->assertEquals(String::format($str, array('str1', 'str2', 'str3')), 'test str1 string str2, str3');
    }

    public function testToCamelCase()
    {
        $str = 'test_to_camel_case';
        $this->assertEquals(String::toCamelCase($str), 'testToCamelCase');

        $this->assertEquals(String::toCamelCase($str, true), 'TestToCamelCase');
    }

    public function testFromCamelCase()
    {
        $str = 'testToCamelCase';
        $this->assertEquals(String::fromCamelCase($str), 'test_to_camel_case');
    }

    public function testReplaceConstants()
    {
        $str = 'Test string {TEST_CONST} {TEST_CONST test';
        $test = String::replaceConstants($str, array('TEST_CONST' => 'test'));
        $this->assertEquals($test, 'Test string test {TEST_CONST test');

        define('TEST_CONST', 'test');

        $str = 'Test string {TEST_CONST} test {TEST_CONST';
        $test = String::replaceConstants($str);
        $this->assertEquals($test, 'Test string test test {TEST_CONST');

        $str = '{TEST_CONST} Test string {TEST_CONST} test {TEST_CONST';
        $test = String::replaceConstants($str);
        $this->assertEquals($test, 'test Test string test test {TEST_CONST');

        $test = String::replaceConstants('test');
        $this->assertEquals($test, 'test');
    }

    public function testStrChop()
    {
        $longtext = "this is some really long text with long words that should be chopped";
        $longlink = "http://thisisareally.longlink/with/lots/of/stupid/paths/";

        // Chop at default length
        $test = String::chop($longtext);
        $this->assertEquals($test, 'this is some really long text with long words that shoul ...');

        // Chop in the middle
        $test = String::chop($longtext, 60, true);
        $this->assertEquals($test, 'this is some really long text  ... ds that should be chopped');

        // Chop a link
        $test = String::chop($longlink, 40, true);
        $this->assertEquals($test, 'http://thisisareally ... f/stupid/paths/');
    }
}
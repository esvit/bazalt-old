<?php

use Framework\System\Session\Session;

class Session_Test extends Tests\BaseCase
{
    protected $session;

    protected function setUp()
    {
        $this->session = new Session('test');
    }

    protected function tearDown()
    {
    }

    public function testNamespace()
    {
        $this->assertEquals('test', $this->session->getNamespace());
    }

    public function testStartOnRegenerate()
    {
        $this->session->getSessionId();

        $this->expectOutputString(
            "ini_set('session.save_handler', files);\n" .
            "ini_set('session.save_path', /tmp);\n" .
            "ini_set('session.gc_maxlifetime', 2000);\n" .
            "session_name(BAZALT);\n" .
            "session_set_cookie_params(2000, /, , false, true);\n" .
            "session_start();\n"
        );
    }

    public function testGetSet()
    {
        $this->session->test = 1;
        $session = new Session('test2');
        $session->test = 1;

        $this->assertEquals(1, $_SESSION['test_test']);
        $this->assertEquals(1, $_SESSION['test2_test']);

        $this->session->test = 2;
        $this->assertEquals(2, $_SESSION['test_test']);
        $this->assertEquals(1, $_SESSION['test2_test']);

        $this->assertEquals(2, $this->session->test);
        $this->assertEquals(1, $session->test);

        unset($session->test);
        $this->assertEquals(2, $this->session->test);
        $this->assertNull($session->test);

        $this->assertTrue(isset($this->session->test));
        $this->assertFalse(isset($session->test));
    }
}
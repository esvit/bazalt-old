<?php

use Framework\System\View\Scope;

class Base_Test extends Tests\BaseCase
{
    protected $view;

    protected function setUp()
    {
        $this->view = Scope::root()->newScope([
            'test' => dirname(__FILE__) . '/templates'
        ]);
    }

    protected function tearDown()
    {
        $this->view = null;
    }

    public function testFolders()
    {
        $this->assertEquals(['test' => dirname(__FILE__) . '/templates'], $this->view->folders());

        $this->view->folders([
            'test' => dirname(__FILE__) . '/templates',
            'test2' => dirname(__FILE__) . '/templates2'
        ]);

        $this->assertEquals([
            'test' => dirname(__FILE__) . '/templates',
            'test2' => dirname(__FILE__) . '/templates2'
        ], $this->view->folders());
    }

    public function testFetch()
    {
        $this->view->assign('test', 'awdawd');

        $this->assertEquals('Test awdawd  Test', $this->view->fetch('test'));
        $this->assertEquals('Test awdawd  Test', $this->view->fetch('test.php'));

        $view = Scope::root()->newScope([
            'test' => dirname(__FILE__) . '/templates'
        ]);

        $view->test2 = 'qweqwe';
        $this->assertEquals('Test  qweqwe Test', $view->fetch('test'));
        $this->assertEquals('Test  qweqwe Test', $view->fetch('test.php'));


        $this->assertEquals('Test test  Test', $view->fetch('test', ['test' => 'test']));
        $this->assertEquals('Test  test2 Test', $view->fetch('test', ['test2' => 'test2']));
    }

    public function testFetchArray()
    {
        $this->assertEquals('Test1', $this->view->fetch(['test1', 'test2']));

        $this->assertEquals('Test2', $this->view->fetch(['test-invalid', 'test2']));

        $this->assertEquals('Test1', $this->view->fetch(['test1.php', 'test-invalid', 'test2']));
    }

    /**
     * @expectedException Exception
     */
    public function testFetchError()
    {
        $this->assertEquals('-', $this->view->fetch('test-invalid'));
    }

    public function testDisplay()
    {
        $this->view->display('test', ['test' => 'test']);

        $this->expectOutputString('Test test  Test');
    }
}
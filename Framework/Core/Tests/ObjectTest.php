<?php

use Framework\Core\Object;

class Exception_Singleton_InterfaceClass extends Object {}

class SingletonClass extends Object implements Framework\Core\Interfaces\Singleton
{
    public $test = 'test';
}

class ObjectNameTestClass extends Object
{
    public function __construct($name = null)
    {
        parent::__construct($name);
    }
}

class ObjectPropertyTestClass extends Object
{
    private $testPrivate = 'test';

    protected $test1 = 'test';

    protected $test3 = 'test';

    public $test = 'test';

    public function __construct($name = null)
    {
        parent::__construct($name);
    }

    public function getTest2()
    {
        return 'test2';
    }

    public function setTest3($value)
    {
        $this->test3 = $value;
    }
}

class ObjectTest extends Tests\BaseCase
{
    public function testGetAllObjects()
    {
        SingletonClass::Singleton();
        $arr = Object::getAllObjects();
        $arr = array_keys($arr);
        $this->assertTrue(in_array('singletonclass', $arr));
    }

    /**
     * @expectedException Framework\Core\Exception\InvalidInterface
     */
    public function testException_Singleton_Interface()
    {
        Exception_Singleton_InterfaceClass::Singleton();
    }

    /**
     * expectedException Framework\Core\Exception\InvalidInterface
     */
    public function testSingleton()
    {
        $obj = SingletonClass::Singleton();

        $this->assertEquals($obj->test, 'test');

        $obj->test = 'test2';

        $obj2 = SingletonClass::Singleton();
        $this->assertEquals($obj2->test, 'test2');

        $stack = array();
        $this->assertEquals(0, count($stack));
 
        array_push($stack, 'foo');
        $this->assertEquals('foo', $stack[count($stack)-1]);
        $this->assertEquals(1, count($stack));
 
        $this->assertEquals('foo', array_pop($stack));
        $this->assertEquals(0, count($stack));
    }

    public function testGetProperty()
    {
        $obj1 = new ObjectPropertyTestClass();
        $this->assertEquals($obj1->Test1,'test');
        $this->assertEquals($obj1->Test2,'test2');
    }

    /**
     * @expectedException Framework\Core\Exception\Property
     */
    public function testGetPrivateExceptionProperty()
    {
        $obj1 = new ObjectPropertyTestClass();
        $this->assertEquals($obj1->TestPrivate,'test');
    }

    /**
     * @expectedException Framework\Core\Exception\Singleton
     */
    public function testCloneException()
    {
        $class = SingletonClass::Singleton();
        $class2 = clone $class;
    }

    public function testClone()
    {
        $class = new ObjectNameTestClass();
        $class2 = clone $class;
        $this->assertEquals($class, $class2);
    }

    public function testGetLogger()
    {
        $class = new ObjectNameTestClass();
        $logger = $class->getLogger();
        $this->assertEquals(get_class($logger), 'Framework\Core\Logger');
    }

    public function testGetPublicProperty()
    {
        $obj1 = new ObjectPropertyTestClass();
        $this->assertEquals($obj1->Test,'test');
    }

    public function testGetHash()
    {
        $class = new ObjectNameTestClass();
        $this->assertEquals(spl_object_hash($class), $class->getHash());
    }

    /**
     * @expectedException Framework\Core\Exception\Property
     */
    public function testGetException_Property()
    {
        $obj1 = new ObjectPropertyTestClass();
        $val = $obj1->testUndef;
    }

    public function testSetProperty()
    {
        $obj1 = new ObjectPropertyTestClass();
        $obj1->Test3 = 'test3';
        $this->assertEquals($obj1->Test3,'test3');
    }

    /**
     * @expectedException Framework\Core\Exception\Property
     */
    public function testSetException_PropertyWhenPropertyHaventSetter()
    {
        $obj1 = new ObjectPropertyTestClass();
        $obj1->Test1 = 'test';
    }

    /**
     * @expectedException Framework\Core\Exception\Property
     */
    public function testSetException_PropertyWhenPropertyUndefined()
    {
        $obj1 = new ObjectPropertyTestClass();
        $obj1->testUndef = 'test';
    }
}
<?php

namespace Framework\CMS\tests\Model;

use Framework\CMS\Model\Component;
use Bazalt\ORM;

class ComponentTest extends \Tests\BaseCase
{
    /**
     * @var Component
     */
    protected $component = null;

    protected function setUp()
    {
        $q = ORM::delete('Framework\CMS\Model\Component');
        $q->exec();

        $this->component = Component::create('test', 'test2');
        $this->component->save();
    }

    protected function tearDown()
    {
        if ($this->component)
            $this->component->delete();
    }

    public function testCreate()
    {
        $this->assertEquals('test', $this->component->name);
        $this->assertEquals('test2', $this->component->title);
    }
}
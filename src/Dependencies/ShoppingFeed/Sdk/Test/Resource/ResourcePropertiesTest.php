<?php

namespace ShoppingFeed\Sdk\Resource;

use ShoppingFeed\Sdk\Exception\RuntimeException;

class ResourcePropertiesTest extends \PHPUnit_Framework_TestCase
{
    public function testAccessProperty()
    {
        $instance = new ResourceProperties(['test' => true]);
        $this->assertNull($instance['toto']);
        $this->assertTrue($instance['test']);
    }

    public function testTestProperty()
    {
        $instance = new ResourceProperties(['test' => true]);
        $this->assertFalse(isset($instance['toto']));
        $this->assertTrue(isset($instance['test']));
    }

    public function testCannotAddProperty()
    {
        $this->expectException(RuntimeException::class);
        $instance = new ResourceProperties([]);
        $instance['test'] = 'true';
    }

    public function testCannotRemoveProperty()
    {
        $this->expectException(RuntimeException::class);
        $instance = new ResourceProperties(['test' => true]);
        unset($instance['test']);
    }


    public function testCannotChangeProperty()
    {
        $this->expectException(RuntimeException::class);
        $instance = new ResourceProperties(['test' => true]);
        $instance['test'] = false;
    }
}


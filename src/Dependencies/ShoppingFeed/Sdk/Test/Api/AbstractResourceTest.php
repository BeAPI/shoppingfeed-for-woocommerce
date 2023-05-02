<?php
namespace ShoppingFeed\Sdk\Test\Api;

use PHPUnit\Framework\TestCase;
use ShoppingFeed\Sdk\Hal\HalResource;

abstract class AbstractResourceTest extends TestCase
{
    /**
     * @var HalResource|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $halResource;

    /**
     * @var array
     */
    protected $props = [];

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|HalResource
     */
    protected function initHalResource()
    {
        $this->halResource = $this->createMock(HalResource::class);

        // supports initialization by default
        $this->halResource->method('get')->willReturnSelf();

        return $this->halResource;
    }

    /**
     * @param array $props
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|HalResource
     */
    protected function initHalResourceProperties(array $props = [])
    {
        if (! $props) {
            $props = $this->props;
        }

        $resource = $this->initHalResource();
        $resource
            ->expects($this->any())
            ->method('getProperty')
            ->with($this->logicalOr(...array_keys($props)))
            ->will($this->returnCallback(function($prop) use($props) {
                return $props[$prop];
            }));

        return $resource;
    }
}

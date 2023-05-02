<?php
namespace ShoppingFeed\Sdk\Test\Api\Store;

use PHPUnit\Framework\TestCase;
use ShoppingFeed\Sdk;

class StoreCollectionTest extends TestCase
{
    private $resourcesData = [
        ['id' => 123, 'name' => 'abc123'],
        ['id' => 456, 'name' => 'abc456'],
        ['id' => 789, 'name' => 'abc789'],
        ['id' => 159, 'name' => 'abc159'],
    ];

    public function testGetById()
    {
        $instance = new Sdk\Api\Store\StoreCollection($this->getResources('id'));

        $resource = $instance->getById(456);

        $this->assertInstanceOf(Sdk\Api\Store\StoreResource::class, $resource);
        $this->assertEquals('abc456', $resource->getName());
        $this->assertEquals(456, $resource->getId());
    }

    public function testGetByIdWithNoMatch()
    {
        $instance = new Sdk\Api\Store\StoreCollection([]);
        $this->assertNull($instance->getById(456));
    }

    public function testGetByName()
    {
        $instance = new Sdk\Api\Store\StoreCollection($this->getResources('name'));

        $resource = $instance->getByName('abc789');

        $this->assertInstanceOf(Sdk\Api\Store\StoreResource::class, $resource);
        $this->assertEquals('abc789', $resource->getName());
        $this->assertEquals(789, $resource->getId());
    }

    public function testGetByNameWithNoMatch()
    {
        $instance = new Sdk\Api\Store\StoreCollection([]);
        $this->assertNull($instance->getByName('abc789'));
    }

    public function testSelect()
    {
        $instance = $this
            ->getMockBuilder(Sdk\Api\Store\StoreCollection::class)
            ->setConstructorArgs([])
            ->setMethods(['getById', 'getByName'])
            ->getMock();

        $instance
            ->expects($this->exactly(2))
            ->method('getById');
        $instance
            ->expects($this->once())
            ->method('getByName');

        $instance->select(10);
        $instance->select('10');
        $instance->select('abc10');
    }

    /**
     * Initialize resources
     *
     * @param string $type
     *
     * @return array
     */
    private function getResources($type)
    {
        $resources = [];
        foreach ($this->resourcesData as $data) {
            $resources[] = $this->getNewResource($data, $type);
        }

        return $resources;
    }

    /**
     * Init new AbstractResource from $data
     *
     * @param array  $data
     * @param string $type
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getNewResource($data, $type = 'id')
    {
        // Used in tests to assert we have the correct resource
        $halResource = $this->createMock(Sdk\Hal\HalResource::class);
        $halResource
            ->method('getProperty')
            ->with($this->logicalOr('name', 'id'))
            ->will($this->returnCallback(
                function ($type) use ($data) {
                    return $data[$type];
                }
            ));

        $resource = $this->getMockBuilder(Sdk\Api\Store\StoreResource::class)
            ->setConstructorArgs([$halResource])
            ->setMethods(['propertyMatch'])
            ->getMock();
        $resource
            ->method('propertyMatch')
            ->will($this->returnValueMap($this->getResourceMap($data, $type)));

        return $resource;
    }

    /**
     * Build map with all data to simulate the check of propertyMatch()
     *
     * @param array  $data
     * @param string $type
     *
     * @return array
     */
    private function getResourceMap(array $data, $type)
    {
        $map = [];
        foreach ($this->resourcesData as $rData) {
            $map[] = [
                $type,
                $rData[$type],
                (bool) ($rData[$type] == $data[$type]),
            ];
        }

        return $map;
    }
}

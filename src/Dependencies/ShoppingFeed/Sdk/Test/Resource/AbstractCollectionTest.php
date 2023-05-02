<?php
namespace ShoppingFeed\Sdk\Test\Resource;

use PHPUnit\Framework\TestCase;
use ShoppingFeed\Sdk\Hal;
use ShoppingFeed\Sdk\Resource;

class AbstractCollectionTest extends TestCase
{
    /**
     * @var array
     */
    private $data;

    /**
     * @var Resource\AbstractCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collection;

    public function setUp()
    {
        for ($i = 0; $i < 10; $i++) {
            $this->data[] = $this->createMock(Hal\HalResource::class);
        }

        $this->collection = new CollectionMock($this->data);
    }

    public function testAddResources()
    {
        foreach ($this->collection as $resource) {
            $this->assertInstanceOf(ResourceMock::class, $resource);
        }
    }

    public function testCount()
    {
        $this->assertCount(count($this->data), $this->collection);
    }

    public function testMerge()
    {
        $this->collection->merge(new CollectionMock($this->data));

        $this->assertCount(count($this->data) * 2, $this->collection);
    }

    public function testToArray()
    {
        $expected = $this->dataToArray();

        $this->assertEquals($expected, $this->collection->toArray());
    }

    public function testJsonSerialization()
    {
        $expected = $this->dataToArray();

        $this->assertEquals($expected, $this->collection->jsonSerialize());
    }

    public function testIterator()
    {
        $expected = new \ArrayIterator(
            array_map(
                function (Hal\HalResource $resource) {
                    return new ResourceMock($resource);
                },
                $this->data
            )
        );

        $this->assertEquals($expected, $this->collection->getIterator());
    }

    /**
     * @return array
     */
    private function dataToArray()
    {
        $expected = [];
        foreach ($this->data as $resource) {
            $expected[] = (new ResourceMock($resource))->toArray();
        }

        return $expected;
    }
}

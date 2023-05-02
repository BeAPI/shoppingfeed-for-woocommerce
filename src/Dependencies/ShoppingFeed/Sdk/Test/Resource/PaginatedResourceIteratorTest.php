<?php

namespace ShoppingFeed\Sdk\Resource;

class PaginatedResourceIteratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PaginatedResourceIterator
     */
    private $instance;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|PaginatedResourceCollection
     */
    private $collection;

    public function setUp()
    {
        $this->collection = $this->createMock(PaginatedResourceCollection::class);
        $this->instance   = new PaginatedResourceIterator($this->collection);
    }

    public function testGetTotalCount()
    {
        $this->collection
            ->expects($this->once())
            ->method('getTotalCount')
            ->willReturn(100);

        $this->assertSame(100, $this->instance->count());
    }

    public function testGetMeta()
    {
        $this->collection
            ->expects($this->once())
            ->method('getMeta')
            ->with('test')
            ->willReturn('toto');

        $this->assertSame('toto', $this->instance->getMeta('test'));
    }

    public function testGetIteratorLoopOverInnerCollection()
    {
        $this->collection
            ->expects($this->exactly(2))
            ->method('next')
            ->willReturnOnConsecutiveCalls($this->collection, null);

        $this->collection
            ->expects($this->exactly(2))
            ->method('getIterator')
            ->willReturn(new \ArrayIterator(['a']));

        $result = iterator_to_array($this->instance->getIterator());
        $this->assertSame(['a', 'a'], $result);
    }
}


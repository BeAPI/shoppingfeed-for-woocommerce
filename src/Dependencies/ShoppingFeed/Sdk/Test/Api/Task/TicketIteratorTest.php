<?php

namespace ShoppingFeed\Sdk\Api\Task;

use ShoppingFeed\Sdk\Exception\RuntimeException;
use ShoppingFeed\Sdk\Resource\PaginatedResourceCollection;

class TicketIteratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|PaginatedResourceCollection
     */
    private $paginator;

    /**
     * @var TicketIterator
     */
    private $instance;

    public function setUp()
    {
        $this->paginator = $this->createMock(PaginatedResourceCollection::class);
        $this->instance = new TicketIterator($this->paginator);
    }

    public function testIsBeingProcessed()
    {
        $this->paginator
            ->expects($this->once())
            ->method('getMeta')
            ->with('processing')
            ->willReturn(true);

        $this->assertTrue(
            $this->instance->isBeingProcessed()
        );
    }

    public function testWaitUntilTicketsAreProcessed()
    {
        $this->paginator
            ->expects($this->exactly(2))
            ->method('getMeta')
            ->with('processing')
            ->willReturnOnConsecutiveCalls([true, false]);

        $this->paginator
            ->expects($this->exactly(1))
            ->method('refresh')
            ->willReturnSelf();

        $result = $this->instance->wait(10, 0);
        $this->assertInstanceOf(TicketIterator::class, $result);
        $this->assertNotSame($this->instance, $result);
    }

    public function testWaitReachTheTimeout()
    {
        $this->paginator
            ->method('getMeta')
            ->willReturn(true);

        $this->paginator
            ->method('refresh')
            ->willReturnSelf();

        $this->expectException(RuntimeException::class);
        $this->instance->wait(1, 0.001);
    }
}


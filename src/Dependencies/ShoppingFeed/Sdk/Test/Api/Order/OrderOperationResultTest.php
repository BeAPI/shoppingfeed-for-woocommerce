<?php

namespace ShoppingFeed\Sdk\Api\Order;

use ShoppingFeed\Sdk\Api\Task\TicketResource;
use ShoppingFeed\Sdk\Hal\HalLink;
use ShoppingFeed\Sdk\Hal\HalResource;

class OrderOperationResultTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var HalResource[]
     */
    private $resources;

    /**
     * @var OrderOperationResult
     */
    private $instance;

    private $link;

    public function setUp()
    {
        $this->link = $this->createMock(HalLink::class);

        $resource = $this->createMock(HalResource::class);
        $resource
            ->expects($this->any())
            ->method('getLink')
            ->with('ticket')
            ->willReturn($this->link);

        $resource
            ->expects($this->exactly(2))
            ->method('getProperty')
            ->with('id')
            ->willReturnOnConsecutiveCalls('a', 'b');

        $this->resources['a'] = $resource;
        $this->resources['b'] = $resource;

        $this->instance = new OrderOperationResult($this->resources);
    }

    public function testBatchIds()
    {
        $this->assertSame(['a', 'b'], $this->instance->getBatchIds());
    }

    public function testWait()
    {
        $this->link
            ->expects($this->exactly(2))
            ->method('get')
            ->willReturn($this->createMock(HalResource::class));

        $this->assertSame(
            $this->instance,
            $this->instance->wait(1, 0.1)
        );
    }

    public function testGetTickets()
    {
        $resource = $this->createMock(HalResource::class);
        $this->link
            ->expects($this->exactly(2))
            ->method('get')
            ->willReturn($resource);

        $resource
            ->expects($this->any())
            ->method('getAllResources')
            ->willReturn([$resource]);

        $this->assertContainsOnly(
            TicketResource::class,
            $this->instance->getTickets()
        );
    }
}


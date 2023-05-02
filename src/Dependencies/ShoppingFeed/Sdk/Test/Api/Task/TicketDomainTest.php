<?php
namespace ShoppingFeed\Sdk\Api\Task;

use PHPUnit\Framework\TestCase;
use ShoppingFeed\Sdk;

class TicketDomainTest extends TestCase
{
    public function testGetByBatch()
    {
        $reference = 'abc213';
        $link      = $this->createMock(Sdk\Hal\HalLink::class);
        $link
            ->expects($this->once())
            ->method('get')
            ->with(
                [],
                ['query' => ['batchId' => $reference, 'page' => 1, 'limit' => 200]]
            )
            ->willReturn($this->createMock(Sdk\Hal\HalResource::class));

        $instance = new Sdk\Api\Task\TicketDomain($link);

        $this->assertInstanceOf(
            TicketIterator::class,
            $instance->getByBatch($reference)
        );
    }
}

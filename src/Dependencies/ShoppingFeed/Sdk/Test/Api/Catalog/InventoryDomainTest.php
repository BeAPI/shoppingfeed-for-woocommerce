<?php
namespace ShoppingFeed\Sdk\Test\Api\Catalog;

use PHPUnit\Framework\TestCase;
use ShoppingFeed\Sdk;

class InventoryDomainTest extends TestCase
{
    public function testGetByReference()
    {
        $reference = 'abc213';
        $link      = $this->createMock(Sdk\Hal\HalLink::class);
        $resource  = $this->createMock(Sdk\Hal\HalResource::class);
        $resource
            ->expects($this->once())
            ->method('getProperty')
            ->with('count')
            ->willReturn(10);
        $resource
            ->expects($this->once())
            ->method('getFirstResource')
            ->with('inventory')
            ->willReturn(
                $this->createMock(Sdk\Hal\HalResource::class)
            );
        $link
            ->expects($this->once())
            ->method('get')
            ->with(
                [],
                ['query' => ['reference' => $reference]]
            )
            ->willReturn($resource);

        $instance = new Sdk\Api\Catalog\InventoryDomain($link);

        $this->assertInstanceOf(Sdk\Api\Catalog\InventoryResource::class, $instance->getByReference($reference));
    }

    public function testGetByReferenceNoInventory()
    {
        $reference = 'abc213';
        $link      = $this->createMock(Sdk\Hal\HalLink::class);
        $resource  = $this->createMock(Sdk\Hal\HalResource::class);
        $resource
            ->expects($this->once())
            ->method('getProperty')
            ->with('count')
            ->willReturn(0);
        $link
            ->expects($this->once())
            ->method('get')
            ->with(
                [],
                ['query' => ['reference' => $reference]]
            )
            ->willReturn($resource);

        $instance = new Sdk\Api\Catalog\InventoryDomain($link);

        $this->assertNull($instance->getByReference($reference));
    }

    public function testExecute()
    {
        $link       = $this->createMock(Sdk\Hal\HalLink::class);
        $operations = $this->createMock(Sdk\Api\Catalog\InventoryUpdate::class);
        $operations
            ->expects($this->once())
            ->method('execute')
            ->with($link)
            ->willReturn($this->createMock(Sdk\Api\Catalog\InventoryCollection::class));

        $instance = new Sdk\Api\Catalog\InventoryDomain($link);
        $instance->execute($operations);
    }
}

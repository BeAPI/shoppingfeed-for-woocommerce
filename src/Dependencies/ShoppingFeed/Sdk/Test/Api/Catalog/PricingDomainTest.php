<?php
namespace ShoppingFeed\Sdk\Test\Api\Catalog;

use PHPUnit\Framework\TestCase;
use ShoppingFeed\Sdk;

class PricingDomainTest extends TestCase
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
            ->with('pricing')
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

        $instance = new Sdk\Api\Catalog\PricingDomain($link);

        $this->assertInstanceOf(Sdk\Api\Catalog\PricingResource::class, $instance->getByReference($reference));
    }

    public function testGetByReferenceNoPricing()
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

        $instance = new Sdk\Api\Catalog\PricingDomain($link);

        $this->assertNull($instance->getByReference($reference));
    }

    public function testExecute()
    {
        $link       = $this->createMock(Sdk\Hal\HalLink::class);
        $operations = $this->createMock(Sdk\Api\Catalog\PricingUpdate::class);
        $operations
            ->expects($this->once())
            ->method('execute')
            ->with($link)
            ->willReturn($this->createMock(Sdk\Api\Catalog\PricingCollection::class));

        $instance = new Sdk\Api\Catalog\PricingDomain($link);
        $instance->execute($operations);
    }
}

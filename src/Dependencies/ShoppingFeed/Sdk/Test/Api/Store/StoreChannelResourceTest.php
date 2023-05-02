<?php

namespace ShoppingFeed\Sdk\Api\Store;

use ShoppingFeed\Sdk;

class StoreChannelResourceTest extends Sdk\Test\Api\AbstractResourceTest
{
    public function testPropertiesGetters()
    {
        $hal = $this->initHalResourceProperties([
            'id'        => 10,
            'installed' => true,
        ]);

        $instance = new Sdk\Api\Store\StoreChannelResource($hal);

        $this->assertEquals(10, $instance->getId());
        $this->assertEquals(true, $instance->isInstalled());
    }

    public function testChannelPropertiesGetters()
    {
        $hal = $this->initHalResource();
        $hal
            ->expects($this->once())
            ->method('getFirstResource')
            ->with('channel')
            ->willReturnSelf();

        $hal
            ->expects($this->any())
            ->method('getProperty')
            ->withConsecutive(['name'], ['segment'], ['type'])
            ->willReturnOnConsecutiveCalls('amazon', 'all', 'marketplace');


        $instance = new Sdk\Api\Store\StoreChannelResource($hal);
        $this->assertSame(
            'amazon',
            $instance->getName(),
            'name is grabbed from embedded resource'
        );
        $this->assertSame(
            'all',
            $instance->getSegment(),
            'name is grabbed from embedded resource'
        );
        $this->assertSame(
            'marketplace',
            $instance->getType(),
            'name is grabbed from embedded resource'
        );

        $this->assertInstanceOf(
            Sdk\Api\Channel\ChannelResource::class,
            $instance->getChannel(),
            'Channel instance is associated to the resource'
        );
    }
}


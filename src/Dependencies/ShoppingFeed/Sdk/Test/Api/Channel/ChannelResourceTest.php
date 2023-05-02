<?php
namespace ShoppingFeed\Sdk\Api\Channel;

use ShoppingFeed\Sdk\Hal\HalLink;
use ShoppingFeed\Sdk\Test\Api\AbstractResourceTest;

class ChannelResourceTest extends AbstractResourceTest
{
    public function testPartialAccessors()
    {
        //Set up regular properties
        $this->initHalResourceProperties([
            'id'   => 1,
            'name' => 'toto'
        ]);

        // Setup image link
        $link = $this->createMock(HalLink::class);
        $link
            ->method('getHref')
            ->willReturn('http://toto.jpg');

        $this->halResource
            ->method('getLink')
            ->with('image')
            ->willReturn($link);

        // Expectations time
        $instance = new ChannelResource($this->halResource);

        $this->assertSame(1, $instance->getId());
        $this->assertSame('toto', $instance->getName());
        $this->assertSame('http://toto.jpg', $instance->getLogoUrl());
    }

    public function testOtherAccessors()
    {
        //Set up regular properties
        $this->initHalResourceProperties($props = [
            'segment'   => 'shoes',
            'countries' => ['FR', 'US'],
            'type'      => 'marketplace'
        ]);

        // Expectations time
        $instance = new ChannelResource($this->halResource);
        $this->assertSame($props['segment'], $instance->getSegment());
        $this->assertSame($props['countries'], $instance->getCountryCodes());
        $this->assertSame($props['type'], $instance->getType());
    }
}


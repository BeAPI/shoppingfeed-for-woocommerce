<?php
namespace ShoppingFeed\Sdk\Test\Hal;

use PHPUnit\Framework\TestCase;
use ShoppingFeed\Sdk\Hal\HalClient;
use ShoppingFeed\Sdk\Hal\HalLink;
use ShoppingFeed\Sdk\Hal\HalResource;

class HalResourceTest extends TestCase
{
    /**
     * @var array
     */
    private $links;

    /**
     * @var array
     */
    private $embedded;

    /**
     * @var array
     */
    private $properties;

    public function setUp()
    {
        $this->properties = [
            'prop1' => 1,
            'prop2' => 'val2',
            'prop3' => true,
        ];
        $this->embedded   = [
            'rel1' => ['id' => 123],
            'rel2' => ['id' => 456],
        ];
        $this->links      = [
            'link1' => ['href' => 'http://link1'],
            'link2' => ['href' => 'http://link2'],
        ];
    }

    public function testHasProperty()
    {
        $client   = $this->createMock(HalClient::class);
        $instance = new HalResource($client, $this->properties);

        $this->assertTrue($instance->hasProperty('prop1'));
        $this->assertTrue($instance->hasProperty('prop2'));
        $this->assertTrue($instance->hasProperty('prop3'));
    }

    public function testGetProperty()
    {
        $client   = $this->createMock(HalClient::class);
        $instance = new HalResource($client, $this->properties);

        foreach ($this->properties as $prop => $val) {
            $this->assertEquals($val, $instance->getProperty($prop));
        }
    }

    public function testGetPropertyDefault()
    {
        $client   = $this->createMock(HalClient::class);
        $instance = new HalResource($client, $this->properties);

        $this->assertEquals(
            'defaultValue',
            $instance->getProperty('unexistingProp', 'defaultValue')
        );
    }

    public function testGetProperties()
    {
        $client   = $this->createMock(HalClient::class);
        $instance = new HalResource($client, $this->properties);

        $this->assertEquals(
            $this->properties,
            $instance->getProperties()
        );
    }

    public function testGetLink()
    {
        $client               = $this->createMock(HalClient::class);
        $this->links['link3'] = new HalLink($client, 'http://link3');
        $instance             = new HalResource($client, [], $this->links);

        $this->assertEquals(
            $this->createsLinks($client)['link1'],
            $instance->getLink('link1')
        );
    }

    public function testGet()
    {
        $client   = $this->createMock(HalClient::class);
        $resource = $this->createMock(HalResource::class);
        $link     = $this->createMock(HalLink::class);
        $link
            ->expects($this->once())
            ->method('get')
            ->willReturn($resource);

        $instance = $this
            ->getMockBuilder(HalResource::class)
            ->setConstructorArgs([$client])
            ->setMethods(['getLink'])
            ->getMock();

        $instance
            ->expects($this->once())
            ->method('getLink')
            ->with('self')
            ->willReturn($link);

        $this->assertEquals($resource, $instance->get());
    }

    public function testGetLinkMissing()
    {
        $client   = $this->createMock(HalClient::class);
        $instance = new HalResource($client, [], $this->links);

        $this->assertNull($instance->getLink('linkMissing'));
    }

    public function testGetResources()
    {
        $client   = $this->createMock(HalClient::class);
        $instance = new HalResource($client, [], [], $this->embedded);

        $this->assertEquals(
            $this->embeddingResources($client)['rel1'],
            $instance->getResources('rel1')
        );
    }

    public function testGetFirstResources()
    {
        $client   = $this->createMock(HalClient::class);
        $instance = $this
            ->getMockBuilder(HalResource::class)
            ->setConstructorArgs([$client, [], [], $this->embedded])
            ->setMethods(['getResources'])
            ->getMock();

        $instance
            ->expects($this->once())
            ->method('getResources')
            ->willReturn([HalResource::fromArray($client, $this->embedded['rel1'])]);

        $this->assertEquals(
            $this->embeddingResources($client)['rel1'][0],
            $instance->getFirstResource('rel1')
        );
    }

    public function testGetFirstResourcesMissing()
    {
        $client   = $this->createMock(HalClient::class);
        $instance = $this
            ->getMockBuilder(HalResource::class)
            ->setConstructorArgs([$client, [], [], $this->embedded])
            ->setMethods(['getResources'])
            ->getMock();

        $instance
            ->expects($this->once())
            ->method('getResources')
            ->willReturn(null);

        $this->assertNull($instance->getFirstResource('rel1'));
    }

    public function testGetResourcesMissing()
    {
        $client   = $this->createMock(HalClient::class);
        $instance = new HalResource($client);

        $this->assertEquals([], $instance->getResources('rel1'));
    }

    public function testGetAllResources()
    {
        $client   = $this->createMock(HalClient::class);
        $instance = new HalResource($client, [], [], $this->embedded);

        $this->assertEquals(
            $this->embeddingResources($client),
            $instance->getAllResources()
        );
    }

    public function testFromArray()
    {
        $client   = $this->createMock(HalClient::class);
        $resource = HalResource::fromArray(
            $client,
            [
                '_links'    => $this->links,
                '_embedded' => $this->embedded,
            ]
        );

        $this->assertInstanceOf(HalResource::class, $resource);
    }

    /**
     * Format embedded data
     *
     * @param HalClient $client
     *
     * @return array
     */
    private function embeddingResources(HalClient $client)
    {
        return array_map(
            function ($resourceData) use ($client) {
                return [HalResource::fromArray($client, $resourceData)];
            },
            $this->embedded
        );
    }

    /**
     * Format embedded data
     *
     * @param HalClient $client
     *
     * @return array
     */
    private function createsLinks(HalClient $client)
    {
        return array_map(
            function ($linkData) use ($client) {
                return $linkData instanceof HalLink ? $linkData : new HalLink($client, $linkData['href'], $linkData);
            },
            $this->links
        );
    }
}

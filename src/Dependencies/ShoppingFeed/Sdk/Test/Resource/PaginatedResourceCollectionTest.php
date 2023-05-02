<?php
namespace ShoppingFeed\Sdk\Test\Resource;

use PHPUnit\Framework\TestCase;
use ShoppingFeed\Sdk\Hal\HalLink;
use ShoppingFeed\Sdk\Hal\HalResource;
use ShoppingFeed\Sdk\Resource\PaginatedResourceCollection;

class PaginatedResourceCollectionTest extends TestCase
{
    public function testGetTotalCount()
    {
        $instance = $this
            ->getMockBuilder(PaginatedResourceCollection::class)
            ->setConstructorArgs(
                [
                    $this->createMock(HalResource::class),
                    ResourceMock::class,
                ]
            )
            ->setMethods(['getProperty'])
            ->getMock();

        $instance
            ->expects($this->once())
            ->method('getProperty')
            ->with('total')
            ->willReturn(15);

        $this->assertEquals(15, $instance->getTotalCount());
    }

    public function testGetCurrentCount()
    {
        $instance = $this
            ->getMockBuilder(PaginatedResourceCollection::class)
            ->setConstructorArgs(
                [
                    $this->createMock(HalResource::class),
                    ResourceMock::class,
                ]
            )
            ->setMethods(['getProperty'])
            ->getMock();

        $instance
            ->expects($this->once())
            ->method('getProperty')
            ->with('count')
            ->willReturn(10);

        $this->assertEquals(10, $instance->getCurrentCount());
    }

    public function testCount()
    {
        $instance = $this
            ->getMockBuilder(PaginatedResourceCollection::class)
            ->setConstructorArgs(
                [
                    $this->createMock(HalResource::class),
                    ResourceMock::class,
                ]
            )
            ->setMethods(['getCurrentCount'])
            ->getMock();

        $instance
            ->expects($this->once())
            ->method('getCurrentCount')
            ->willReturn(18);

        $this->assertEquals(18, $instance->count());
    }

    public function testGetTotalPages()
    {
        $instance = $this
            ->getMockBuilder(PaginatedResourceCollection::class)
            ->setConstructorArgs(
                [
                    $this->createMock(HalResource::class),
                    ResourceMock::class,
                ]
            )
            ->setMethods(['getProperty'])
            ->getMock();

        $instance
            ->expects($this->once())
            ->method('getProperty')
            ->with('pages')
            ->willReturn(5);

        $this->assertEquals(5, $instance->getTotalPages());
    }

    public function testGetCurrentPage()
    {
        $instance = $this
            ->getMockBuilder(PaginatedResourceCollection::class)
            ->setConstructorArgs(
                [
                    $this->createMock(HalResource::class),
                    ResourceMock::class,
                ]
            )
            ->setMethods(['getProperty'])
            ->getMock();

        $instance
            ->expects($this->once())
            ->method('getProperty')
            ->with('page')
            ->willReturn(8);

        $this->assertEquals(8, $instance->getCurrentPage());
    }

    public function testNext()
    {
        $link     = $this->createMock(HalLink::class);
        $resource = $this->createMock(HalResource::class);

        $link
            ->expects($this->once())
            ->method('get')
            ->willReturn($this->createMock(HalResource::class));

        $resource
            ->expects($this->once())
            ->method('getLink')
            ->with('next')
            ->willReturn($link);

        $instance = new PaginatedResourceCollection($resource, ResourceMock::class);

        $this->assertInstanceOf(PaginatedResourceCollection::class, $instance->next());
    }

    public function testNextNoLink()
    {
        $resource = $this->createMock(HalResource::class);

        $resource
            ->expects($this->once())
            ->method('getLink')
            ->with('next')
            ->willReturn(null);

        $instance = new PaginatedResourceCollection($resource, ResourceMock::class);

        $this->assertNull($instance->next());
    }

    public function testNextNoResource()
    {
        $link     = $this->createMock(HalLink::class);
        $resource = $this->createMock(HalResource::class);

        $link
            ->expects($this->once())
            ->method('get')
            ->willReturn(null);

        $resource
            ->expects($this->once())
            ->method('getLink')
            ->with('next')
            ->willReturn($link);

        $instance = new PaginatedResourceCollection($resource, ResourceMock::class);

        $this->assertNull($instance->next());
    }

    public function testGetIterator()
    {
        $resources = [
            $this->createMock(HalResource::class),
            $this->createMock(HalResource::class),
            $this->createMock(HalResource::class),
            $this->createMock(HalResource::class),
        ];
        $resource = $this->createMock(HalResource::class);
        $resource
            ->expects($this->once())
            ->method('getAllResources')
            ->willReturn([$resources]);

        $instance = new PaginatedResourceCollection($resource, ResourceMock::class);

        $count = 0;
        foreach ($instance->getIterator() as $i => $item) {
            $count++;
            $this->assertInstanceOf(ResourceMock::class, $item);
            $this->assertEquals(new ResourceMock($resources[$i]), $item);
        }

        $this->assertEquals(count($resources), $count);
    }

    public function testGetAllMetadata()
    {
        $expected = ['test' => true];
        $resource = $this->createMock(HalResource::class);
        $resource
            ->expects($this->once())
            ->method('getProperty')
            ->with('meta')
            ->willReturn($expected);

        $instance = new PaginatedResourceCollection($resource, ResourceMock::class);
        $this->assertSame($expected, $instance->getMeta());
    }

    public function testGetSpecificMetadata()
    {
        $expected = ['test' => 'data'];
        $resource = $this->createMock(HalResource::class);
        $resource
            ->expects($this->once())
            ->method('getProperty')
            ->with('meta')
            ->willReturn($expected);

        $instance = new PaginatedResourceCollection($resource, ResourceMock::class);
        $this->assertSame('data', $instance->getMeta('test'));
    }

    public function testGetSpecificMetadataIsMissing()
    {
        $resource = $this->createMock(HalResource::class);
        $resource
            ->expects($this->once())
            ->method('getProperty')
            ->with('meta')
            ->willReturn(['test' => 'data']);

        $instance = new PaginatedResourceCollection($resource, ResourceMock::class);
        $this->assertNull($instance->getMeta('notfound'));
    }
}

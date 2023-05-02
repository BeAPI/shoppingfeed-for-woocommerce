<?php
namespace ShoppingFeed\Sdk\Test\Resource;

use PHPUnit\Framework\TestCase;
use ShoppingFeed\Sdk\Hal;
use ShoppingFeed\Sdk\Hal\HalLink;
use ShoppingFeed\Sdk\Hal\HalResource;
use ShoppingFeed\Sdk\Resource\AbstractDomainResource;
use ShoppingFeed\Sdk\Resource\AbstractResource;
use ShoppingFeed\Sdk\Resource\PaginatedResourceCollection;
use ShoppingFeed\Sdk\Resource\PaginatedResourceIterator;

class AbstractDomainResourceTest extends TestCase
{
    public function testGetPageNullOnDeadLink()
    {
        $link = $this->createMock(HalLink::class);
        $link
            ->expects($this->once())
            ->method('get')
            ->willReturn(null);

        $instance = new DomainResourceMock($link);

        $this->assertNull($instance->getPage());
    }

    public function testGetPage()
    {
        $link = $this->createMock(HalLink::class);
        $link
            ->expects($this->once())
            ->method('get')
            ->willReturn($this->createMock(HalResource::class));

        $instance = new DomainResourceMock($link);

        $this->assertInstanceOf(PaginatedResourceCollection::class, $instance->getPage());
    }

    public function testGetPagesAndCreatePaginator()
    {
        // $i is used to generate the awaited level of depth
        $i          = 0;
        $pageFrom   = 1;
        $perPage    = 20;
        $totalItems = 400;

        $link = $this->createMock(HalLink::class);
        $link
            ->expects($this->once())
            ->method('get')
            ->willReturn($this->getPaginatedResource($i, $pageFrom, $perPage, $totalItems));

        $instance = new DomainResourceMock($link);

        $count     = 0;
        $criterias = ['page' => $pageFrom, 'limit' => $perPage];
        foreach ($instance->getPages($criterias) as $collection) {
            $count++;
            $this->assertInstanceOf(PaginatedResourceCollection::class, $collection);
        }

        // NumberItem / ItemPerPage = NumberOfPages,
        // NumberOfPages - (PageToStartAt - 1) = AwaitedNumberOfPages (-1 because page 0 does no exists)
        $this->assertEquals(($totalItems / $perPage) - ($pageFrom - 1), $count);
    }

    public function testGetAll()
    {
        $link = $this->createMock(HalLink::class);

        /** @var DomainResourceMock|\PHPUnit_Framework_MockObject_MockObject $instance */
        $instance = $this
            ->getMockBuilder(DomainResourceMock::class)
            ->setConstructorArgs([$link])
            ->setMethods(['createIterator'])
            ->getMock();

        $iterator = $this->createMock(PaginatedResourceIterator::class);

        $instance
            ->expects($this->once())
            ->method('createIterator')
            ->willReturn($iterator);

        $this->assertEquals($iterator, $instance->getAll());
    }

    public function testGetAllWithNoPaginator()
    {
        $link = $this->createMock(HalLink::class);

        /** @var DomainResourceMock|\PHPUnit_Framework_MockObject_MockObject $instance */
        $instance = $this
            ->getMockBuilder(DomainResourceMock::class)
            ->setConstructorArgs([$link])
            ->setMethods(['createPaginator'])
            ->getMock();

        $instance
            ->expects($this->once())
            ->method('createPaginator')
            ->willReturn(null);

        $this->assertNull($instance->getAll());
    }

    /**
     * Recurse to simulate load of next link
     *
     * @param int &$i
     * @param int $pageFrom
     * @param int $perPage
     * @param int $totalItems
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getPaginatedResource(&$i = 0, $pageFrom = 1, $perPage = 10, $totalItems = 400)
    {
        $i += $perPage;

        $resource = $this
            ->getMockBuilder(HalResource::class)
            ->disableOriginalConstructor()
            ->setMethods(['getLink'])
            ->getMock();

        $link = $this
            ->getMockBuilder(HalLink::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();

        $link
            ->expects($this->once())
            ->method('get')
            ->willReturn(
                $i < $totalItems ? $this->getPaginatedResource($i, $pageFrom, $perPage) : null
            );

        $resource
            ->expects($this->once())
            ->method('getLink')
            ->with('next')
            ->willReturn($link);

        return $resource;
    }

    public function testGetOneLinkToSingleResource()
    {
        $link     = $this->createMock(HalLink::class);
        $resource = $this->createMock(HalResource::class);

        $link
            ->expects($this->once())
            ->method('withAddedHref')
            ->with('123')
            ->willReturnSelf();

        $link
            ->expects($this->once())
            ->method('get')
            ->willReturn($resource);

        $instance = new AbstractDomainResourceStub($link);
        $instance->resourceClass = AbstractDomainResourceResourceStub::class;

        $result = $instance->getOne('123');
        $this->assertInstanceOf(AbstractDomainResourceResourceStub::class, $result);
        $this->assertSame($resource, $result->halResource);
    }
}

class AbstractDomainResourceStub extends AbstractDomainResource
{
    public $resourceClass;
}

class AbstractDomainResourceResourceStub extends AbstractResource
{
    public $halResource;

    public function __construct(Hal\HalResource $resource)
    {
        $this->halResource = $resource;
    }
}

<?php
namespace ShoppingFeed\Sdk\Test\Resource;

use PHPUnit\Framework\TestCase;
use ShoppingFeed\Sdk\Hal\HalClient;
use ShoppingFeed\Sdk\Hal\HalResource;
use ShoppingFeed\Sdk\Resource\AbstractResource;

class AbstractResourceTest extends TestCase
{
    /**
     * @var HalResource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $halResource;

    /**
     * @var array
     */
    private $data;

    public function setUp()
    {
        $this->halResource = $this->createMock(HalResource::class);
        $this->data        = [
            'prop1' => 'value1',
            'prop2' => 1,
            'prop3' => true,
            'prop4' => 'now',
        ];
    }

    /**
     * @return AbstractResource|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getInstance()
    {
        return $this->getMockForAbstractClass(
            AbstractResource::class,
            [$this->halResource],
            '',
            true,
            true,
            true,
            ['initialize']
        );
    }

    public function testRefresh()
    {
        $instance = $this->getInstance();

        $instance
            ->expects($this->once())
            ->method('initialize')
            ->with(true);

        $instance->refresh();
    }

    public function testArraySerialization()
    {
        $this
            ->halResource
            ->expects($this->once())
            ->method('getProperties')
            ->willReturn($this->data);

        $instance = $this->getInstance();

        $this->assertEquals($this->data, $instance->toArray());
    }

    public function testJsonSerialization()
    {
        $this
            ->halResource
            ->expects($this->once())
            ->method('getProperties')
            ->willReturn($this->data);

        $instance = $this->getInstance();

        $this->assertEquals($this->data, $instance->jsonSerialize());
    }

    public function testGetProperty()
    {
        $this
            ->halResource
            ->expects($this->exactly(3))
            ->method('getProperty');

        $instance = new ResourceMock($this->halResource);

        $instance->getProperty('prop1');
        $instance->getProperty('prop2');
        $instance->getProperty('prop3');
    }

    public function testGetPropertyMatch()
    {
        $data = $this->data;
        $this
            ->halResource
            ->expects($this->exactly(3))
            ->method('getProperty')
            ->will(
                $this->returnCallback(
                    function ($prop) use ($data) {
                        return $data[$prop];
                    }
                )
            );

        $instance = new ResourceMock($this->halResource);

        $this->assertTrue($instance->propertyMatch('prop1', 'value1'));
        $this->assertTrue($instance->propertyMatch('prop2', 1));
        $this->assertTrue($instance->propertyMatch('prop3', true));
    }

    /**
     * @throws \Exception
     */
    public function testGetPropertyDatetime()
    {
        $date = '2018-05-04T12:20:07.160557+0200';
        $this
            ->halResource
            ->expects($this->once())
            ->method('getProperty')
            ->with('prop4')
            ->willReturn($date);

        $instance = new ResourceMock($this->halResource);

        $this->assertEquals(new \DateTimeImmutable($date), $instance->getPropertyDatetime('prop4'));
    }

    /**
     * @throws \Exception
     */
    public function testGetPropertyDatetimeNull()
    {
        $this
            ->halResource
            ->expects($this->once())
            ->method('getProperty')
            ->with('prop4')
            ->willReturn(null);

        $instance = new ResourceMock($this->halResource);

        $this->assertNull($instance->getPropertyDatetime('prop4'));
    }

    public function testInitialize()
    {
        $this
            ->halResource
            ->expects($this->once())
            ->method('get');

        $instance = new ResourceMock($this->halResource);

        $instance->initialize(true);
        $this->assertFalse($instance->isPartial());
    }
}

<?php
namespace ShoppingFeed\Sdk\Test\Api\Catalog;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use ShoppingFeed\Sdk;

class InventoryUpdateTest extends TestCase
{
    /**
     * @var array
     */
    private $operations = [
        'abc123'  => 5,
        'abc1234' => 7,
        'abc1235' => 8,
    ];

    public function testConstructWithOperations()
    {
        $instance   = new Sdk\Api\Catalog\InventoryUpdate($this->operations);
        $reflection = new \ReflectionClass($instance);
        $property   = $reflection->getProperty('operations');
        $property->setAccessible(true);

        $result = [];
        foreach ($this->operations as $reference => $quantity) {
            $result[$reference] = compact('reference', 'quantity');
        }

        $this->assertEquals($result, $property->getValue($instance));

    }

    public function testAdd()
    {
        $instance   = new Sdk\Api\Catalog\InventoryUpdate();
        $reflection = new \ReflectionClass($instance);
        $property   = $reflection->getProperty('operations');
        $property->setAccessible(true);

        $result = [];
        foreach ($this->operations as $reference => $quantity) {
            $instance->add($reference, $quantity);
            $result[$reference] = compact('reference', 'quantity');
        }

        $this->assertEquals($result, $property->getValue($instance));
    }

    public function testExecute()
    {
        $link = $this->createMock(Sdk\Hal\HalLink::class);
        $link
            ->expects($this->once())
            ->method('createRequest')
            ->willReturn(
                $this->createMock(RequestInterface::class)
            );

        $instance = $this
            ->getMockBuilder(Sdk\Api\Catalog\InventoryUpdate::class)
            ->setConstructorArgs([$this->operations])
            ->setMethods(['getPoolSize'])
            ->getMock();

        $instance
            ->expects($this->once())
            ->method('getPoolSize')
            ->willReturn(10);

        $link
            ->expects($this->once())
            ->method('batchSend')
            ->with(
                [$this->createMock(RequestInterface::class)],
                function (Sdk\Hal\HalResource $resource) use (&$resources) {
                    array_push($resources, ...$resource->getResources('inventory'));
                },
                null,
                [],
                10
            );

        $this->assertInstanceOf(
            Sdk\Api\Catalog\InventoryCollection::class,
            $instance->execute($link)
        );
    }

    public function testBatchProcessor()
    {
        $requests = [];
        $request  = $this->createMock(RequestInterface::class);
        $link     = $this->createMock(Sdk\Hal\HalLink::class);
        $instance = new Sdk\Api\Catalog\InventoryUpdate(['ref1' => 1]);

        $link
            ->expects($this->once())
            ->method('createRequest')
            ->with('PUT', [], ['inventory' => ['data']])
            ->willReturn($request);

        $reflexion       = new \ReflectionClass($instance);
        $reflexionMethod = $reflexion->getMethod('createBatchProcessorCallback');
        $reflexionMethod->setAccessible(true);

        $callable = $reflexionMethod->invokeArgs($instance, [$link, &$requests]);

        $this->assertInternalType(\PHPUnit_Framework_Constraint_IsType::TYPE_CALLABLE, $callable);

        $callable(['data']);

        $this->assertEquals($request, $requests[0]);
    }

    public function testSuccessCallback()
    {
        $resources = [];
        $resource  = $this->createMock(Sdk\Hal\HalResource::class);
        $instance  = new Sdk\Api\Catalog\InventoryUpdate(['ref1' => 1]);

        $resource
            ->expects($this->once())
            ->method('getResources')
            ->with('inventory')
            ->willReturn([
                $this->createMock(Sdk\Hal\HalResource::class),
                $this->createMock(Sdk\Hal\HalResource::class),
            ]);

        $reflexion       = new \ReflectionClass($instance);
        $reflexionMethod = $reflexion->getMethod('createSuccessCallback');
        $reflexionMethod->setAccessible(true);

        $callable = $reflexionMethod->invokeArgs($instance, [&$resources]);

        $this->assertInternalType(\PHPUnit_Framework_Constraint_IsType::TYPE_CALLABLE, $callable);

        $callable($resource);

        $this->assertEquals($resource, $resources[0]);
    }

    public function testSuccessCallbackWithNoInvetoryReturned()
    {
        $resources = [];
        $resource  = $this->createMock(Sdk\Hal\HalResource::class);
        $instance  = new Sdk\Api\Catalog\InventoryUpdate(['ref1' => 1]);

        $resource
            ->expects($this->once())
            ->method('getResources')
            ->with('inventory')
            ->willReturn([]);

        $reflexion       = new \ReflectionClass($instance);
        $reflexionMethod = $reflexion->getMethod('createSuccessCallback');
        $reflexionMethod->setAccessible(true);

        $callable = $reflexionMethod->invokeArgs($instance, [&$resources]);

        $this->assertInternalType(\PHPUnit_Framework_Constraint_IsType::TYPE_CALLABLE, $callable);

        $callable($resource);

        $this->assertInternalType('array', $resources);
        $this->assertEmpty($resources);
    }
}

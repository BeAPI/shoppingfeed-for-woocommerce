<?php
namespace ShoppingFeed\Sdk\Test\Api\Order;

use GuzzleHttp\Psr7;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;
use ShoppingFeed\Sdk;

class OrderOperationTest extends TestCase
{
    private $operationCount = 10;

    /**
     * Generate operations
     *
     * @param Sdk\Api\Order\OrderOperation $orderOperation
     *
     * @throws \Exception
     */
    private function generateOperations(Sdk\Api\Order\OrderOperation $orderOperation)
    {
        for ($i = 0; $i < $this->operationCount; $i++) {
            $orderOperation->addOperation(
                'ref' . $i,
                'amazon',
                Sdk\Api\Order\OrderOperation::TYPE_ACCEPT
            );
        }
    }

    /**
     * @throws \Exception
     */
    public function testAddOperation()
    {
        $orderOperation = new Sdk\Api\Order\OrderOperation();
        $this->generateOperations($orderOperation);

        $this->assertEquals(
            $this->operationCount,
            $orderOperation->count(Sdk\Api\Order\OrderOperation::TYPE_ACCEPT)
        );
    }

    /**
     * @throws \Exception
     */
    public function testAcceptOperation()
    {
        $instance = $this
            ->getMockBuilder(Sdk\Api\Order\OrderOperation::class)
            ->setMethods(['addOperation'])
            ->getMock();

        $instance
            ->expects($this->once())
            ->method('addOperation')
            ->with(
                'ref1',
                'amazon',
                Sdk\Api\Order\OrderOperation::TYPE_ACCEPT,
                ['reason' => 'noreason']
            );

        $this->assertInstanceOf(
            Sdk\Api\Order\OrderOperation::class,
            $instance->accept(
                'ref1',
                'amazon',
                'noreason'
            )
        );
    }

    /**
     * @throws \Exception
     */
    public function testCancelOperation()
    {
        $instance = $this
            ->getMockBuilder(Sdk\Api\Order\OrderOperation::class)
            ->setMethods(['addOperation'])
            ->getMock();

        $instance
            ->expects($this->once())
            ->method('addOperation')
            ->with(
                'ref1',
                'amazon',
                Sdk\Api\Order\OrderOperation::TYPE_CANCEL,
                ['reason' => 'noreason']
            );

        $this->assertInstanceOf(
            Sdk\Api\Order\OrderOperation::class,
            $instance->cancel(
                'ref1',
                'amazon',
                'noreason'
            )
        );
    }

    /**
     * @throws \Exception
     */
    public function testRefuseOperation()
    {
        $instance = $this
            ->getMockBuilder(Sdk\Api\Order\OrderOperation::class)
            ->setMethods(['addOperation'])
            ->getMock();

        $instance
            ->expects($this->once())
            ->method('addOperation')
            ->with(
                'ref1',
                'amazon',
                Sdk\Api\Order\OrderOperation::TYPE_REFUSE
            );

        $this->assertInstanceOf(
            Sdk\Api\Order\OrderOperation::class,
            $instance->refuse(
                'ref1',
                'amazon'
            )
        );
    }

    /**
     * @throws \Exception
     */
    public function testShipOperation()
    {
        $instance = $this
            ->getMockBuilder(Sdk\Api\Order\OrderOperation::class)
            ->setMethods(['addOperation'])
            ->getMock();

        $instance
            ->expects($this->once())
            ->method('addOperation')
            ->with(
                'ref1',
                'amazon',
                Sdk\Api\Order\OrderOperation::TYPE_SHIP,
                [
                    'carrier'        => 'ups',
                    'trackingNumber' => '123654abc',
                    'trackingLink'   => 'http://tracking.lnk',
                ]
            );

        $this->assertInstanceOf(
            Sdk\Api\Order\OrderOperation::class,
            $instance->ship(
                'ref1',
                'amazon',
                'ups',
                '123654abc',
                'http://tracking.lnk'
            )
        );
    }

    /**
     * @throws \Exception
     */
    public function testAcknowledgeOperation()
    {
        $data = [
            'ref1',
            'amazon',
            '123654abc',
            'success',
            'Acknowledged',
        ];

        $instance = $this
            ->getMockBuilder(Sdk\Api\Order\OrderOperation::class)
            ->setMethods(['addOperation'])
            ->getMock();

        $instance
            ->expects($this->once())
            ->method('addOperation')
            ->with(
                'ref1',
                'amazon',
                Sdk\Api\Order\OrderOperation::TYPE_ACKNOWLEDGE,
                new \PHPUnit_Framework_Constraint_Callback(
                    function ($param) use ($data) {
                        return $param['status'] === 'success'
                               && $param['storeReference'] === '123654abc'
                               && $param['message'] === 'Acknowledged'
                               && !empty($param['acknowledgedAt']);
                    }
                )
            );

        $this->assertInstanceOf(
            Sdk\Api\Order\OrderOperation::class,
            $instance->acknowledge(...$data)
        );
    }

    /**
     * @throws \Exception
     */
    public function testUnacknowledgeOperation()
    {
        $data = [
            'ref2',
            'amazon2',
            'Unacknowledged',
        ];
        $instance = $this
            ->getMockBuilder(Sdk\Api\Order\OrderOperation::class)
            ->setMethods(['addOperation'])
            ->getMock();

        $instance
            ->expects($this->once())
            ->method('addOperation')
            ->with(
                'ref2',
                'amazon2',
                Sdk\Api\Order\OrderOperation::TYPE_UNACKNOWLEDGE
            );

        $this->assertInstanceOf(
            Sdk\Api\Order\OrderOperation::class,
            $instance->unacknowledge(...$data)
        );
    }

    /**
     * @throws \Exception
     */
    public function testAddWrongOperation()
    {
        $orderOperation = new Sdk\Api\Order\OrderOperation();

        $this->expectException(Sdk\Exception\InvalidArgumentException::class);

        $orderOperation->addOperation(
            'ref',
            'amazon',
            'FakeType'
        );
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

        /** @var Sdk\Api\Order\OrderOperation|\PHPUnit_Framework_MockObject_MockObject $instance */
        $instance = $this
            ->getMockBuilder(Sdk\Api\Order\OrderOperation::class)
            ->setMethods(['getPoolSize'])
            ->getMock();

        $instance
            ->expects($this->once())
            ->method('getPoolSize')
            ->willReturn(10);

        $this->generateOperations($instance);

        $link
            ->expects($this->once())
            ->method('batchSend')
            ->with(
                [$this->createMock(RequestInterface::class)],
                function (Sdk\Hal\HalResource $resource) use (&$resources) {
                    array_push($resources, ...$resource->getResources('order'));
                },
                null,
                [],
                10
            );

        $this->assertInstanceOf(
            Sdk\Api\Order\OrderOperationResult::class,
            $instance->execute($link)
        );
    }

    /**
     * @throws \Exception
     */
    public function testRefundOperation()
    {
        $instance = $this
            ->getMockBuilder(Sdk\Api\Order\OrderOperation::class)
            ->setMethods(['addOperation'])
            ->getMock();

        $instance
            ->expects($this->once())
            ->method('addOperation')
            ->with(
                'ref1',
                'amazon',
                Sdk\Api\Order\OrderOperation::TYPE_REFUND,
                [
                    'refund' => [
                        'shipping' => true,
                        'products' => [
                            ['reference' => 'item1', 'quantity' => 1],
                            ['reference' => 'item2', 'quantity' => 2],
                        ]
                    ]
                ]
            );

        $this->assertInstanceOf(
            Sdk\Api\Order\OrderOperation::class,
            $instance->refund(
                'ref1',
                'amazon',
                true,
                [
                    ['reference' => 'item1', 'quantity' => 1],
                    ['reference' => 'item2', 'quantity' => 2],
                ]
            )
        );
    }
}

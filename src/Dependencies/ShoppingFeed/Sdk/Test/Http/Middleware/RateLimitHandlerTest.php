<?php
namespace ShoppingFeed\Sdk\Test\Http\Middleware;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message;
use Psr\Log\LoggerInterface;
use ShoppingFeed\Sdk\Http\Middleware\RateLimitHandler;

class RateLimitHandlerTest extends TestCase
{
    /**
     * @var Message\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var RateLimitHandler
     */
    private $instance;

    /**
     * @var int
     */
    private $limit = 10;

    public function setUp()
    {
        $this->logger   = $this->createMock(LoggerInterface::class);
        $this->request  = $this->createMock(Message\RequestInterface::class);
        $this->instance = new RateLimitHandler(
            $this->limit,
            $this->logger
        );
    }

    public function testDecideAfterMaxRetries()
    {
        $this->assertFalse($this->instance->decide(20, $this->request));
    }

    public function testDecideWithNoResponse()
    {
        $this->assertFalse($this->instance->decide(5, $this->request));
    }

    public function testDecideWithWrongResponseStatus()
    {
        $response = $this->createMock(Message\ResponseInterface::class);
        $response
            ->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(200);

        $this->assertFalse($this->instance->decide(5, $this->request, $response));
    }

    public function testDecideWithRightResponseStatus()
    {
        $response = $this->createMock(Message\ResponseInterface::class);
        $response
            ->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(429);

        $this->assertTrue($this->instance->decide(5, $this->request, $response));
    }

    public function testDelay()
    {
        $limitWait = 440;
        $response  = $this->createMock(Message\ResponseInterface::class);
        $response
            ->expects($this->once())
            ->method('getHeaderLine')
            ->with('X-RateLimit-Wait')
            ->willReturn($limitWait);

        $this->logger
            ->expects($this->once())
            ->method('notice');

        $this->assertEquals($limitWait * 1000, $this->instance->delay(null, $response));
    }
}

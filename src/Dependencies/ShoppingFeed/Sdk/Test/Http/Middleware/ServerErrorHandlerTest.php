<?php
namespace ShoppingFeed\Sdk\Test\Http\Middleware;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message;
use ShoppingFeed\Sdk\Http\Middleware\ServerErrorHandler;

class ServerErrorHandlerTest extends TestCase
{

    /**
     * @var int
     */
    private $limit = 10;

    /**
     * @var array
     */
    private $validCodes = [500, 502, 503, 504];

    /**
     * @var Message\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var ServerErrorHandler
     */
    private $instance;

    public function setUp()
    {
        $this->request  = $this->createMock(Message\RequestInterface::class);
        $this->instance = new ServerErrorHandler($this->limit);
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
        foreach ($this->validCodes as $code) {
            $response = $this->createMock(Message\ResponseInterface::class);
            $response
                ->expects($this->once())
                ->method('getStatusCode')
                ->willReturn($code);

            $this->assertTrue($this->instance->decide(5, $this->request, $response));
        }
    }
}

<?php
namespace ShoppingFeed\Sdk\Test\Http\Adapter;

use GuzzleHttp;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use ShoppingFeed\Sdk\Client\ClientOptions;
use ShoppingFeed\Sdk\Hal\HalResource;
use ShoppingFeed\Sdk\Http\Adapter\AdapterInterface;
use ShoppingFeed\Sdk\Http\Adapter\Guzzle6Adapter;

class Guzzle6AdapterTest extends TestCase
{
    public function testConfigure()
    {
        $instance = new Guzzle6Adapter();
        $options  = new ClientOptions();
        $options->setBaseUri('http://test');
        $options->setRetryOnServerError(20);
        $options->handleRateLimit();

        $adapter = $instance->configure($options);

        $reflexion = new \ReflectionClass($instance);
        $property  = $reflexion->getProperty('options');
        $property->setAccessible(true);
        $extract = $property->getValue($instance);

        $this->assertSame($options, $extract);
        $this->assertInstanceOf(AdapterInterface::class, $adapter);
        $this->assertEquals($adapter, $instance);
    }

    public function testSend()
    {
        $client      = $this->createMock(GuzzleHttp\Client::class);
        $mockHandler = GuzzleHttp\HandlerStack::create(
            new GuzzleHttp\Handler\MockHandler([new GuzzleHttp\Psr7\Response(200)])
        );
        $options     = new ClientOptions();
        $request     = $this->createMock(RequestInterface::class);
        $response    = $this->createMock(ResponseInterface::class);

        $options->setBaseUri('http://test.com/');

        $client
            ->expects($this->once())
            ->method('send')
            ->with($request)
            ->willReturn($response);

        /** @var Guzzle6Adapter|\PHPUnit_Framework_MockObject_MockObject $instance */
        $instance = new Guzzle6Adapter($options, $mockHandler);

        $reflexion = new \ReflectionClass($instance);
        $property  = $reflexion->getProperty('client');
        $property->setAccessible(true);
        $property->setValue($instance, $client);

        $result = $instance->send($request);

        $this->assertSame($response, $result);
    }

    public function testBatchSend()
    {
        $client      = $this->createMock(GuzzleHttp\Client::class);
        $mockHandler = GuzzleHttp\HandlerStack::create(
            new GuzzleHttp\Handler\MockHandler([new GuzzleHttp\Psr7\Response(200)])
        );
        $options     = new ClientOptions();
        $request     = $this->createMock(RequestInterface::class);

        /** @var Guzzle6Adapter|\PHPUnit_Framework_MockObject_MockObject $instance */
        $instance = new Guzzle6Adapter($options, $mockHandler);

        $reflexion = new \ReflectionClass($instance);

        $propertyClient = $reflexion->getProperty('client');
        $propertyClient->setAccessible(true);
        $propertyClient->setValue($instance, $client);

        $propertyPool = $reflexion->getProperty('pool');
        $propertyPool->setAccessible(true);

        $instance->batchSend([$request]);
        $pool = $propertyPool->getValue($instance);

        $this->assertInstanceOf(GuzzleHttp\Pool::class, $pool);
    }

    public function testCreateRequest()
    {
        /** @var Guzzle6Adapter|\PHPUnit_Framework_MockObject_MockObject $instance */
        $instance = new Guzzle6Adapter();

        $request = $instance->createRequest('GET', '/test', ['FooHeader' => 'BarHeader'], '{"foo":"bar"}');

        $this->assertInstanceOf(RequestInterface::class, $request);
        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals('/test', $request->getUri());
        $this->assertEquals(['FooHeader' => ['BarHeader']], $request->getHeaders());
        $this->assertEquals('{"foo":"bar"}', $request->getBody());
    }

    public function testWithToken()
    {
        $client      = $this->createMock(GuzzleHttp\Client::class);
        $options     = new ClientOptions();
        $mockHandler = GuzzleHttp\HandlerStack::create(function ($request, $options) {
            return $request;
        });

        /** @var Guzzle6Adapter|\PHPUnit_Framework_MockObject_MockObject $instance */
        $instance = new Guzzle6Adapter($options, $mockHandler);
        $request  = $instance->createRequest('GET', '/test', ['FooHeader' => 'BarHeader'], '{"foo":"bar"}');

        $reflexion = new \ReflectionClass($instance);
        $property  = $reflexion->getProperty('client');
        $property->setAccessible(true);
        $property->setValue($instance, $client);

        $token  = '23fds1d654g65sfd3g2qs1fdgh6j54d';
        $result = $instance->withToken($token);

        $reflexionRes = new \ReflectionClass($instance);
        $propertyRes  = $reflexionRes->getProperty('stack');
        $propertyRes->setAccessible(true);

        /** @var GuzzleHttp\HandlerStack $stack */
        $stack   = $propertyRes->getValue($result);
        $request = $stack($request, []);

        $this->assertInstanceOf(Guzzle6Adapter::class, $result);
        $this->assertEquals(['Bearer ' . $token], $request->getHeader('Authorization'));
    }

    public function testRetryCountHandlerIsSet()
    {
        $options = new ClientOptions();
        $options->setRetryOnServerError(10);

        $stack = $this->extractStack($options);

        $handlerPresent = false;
        foreach ($stack as $handler) {
            if ($handler[1] === 'retry_count') {
                $handlerPresent = true;
                break;
            }
        }

        $this->assertTrue($handlerPresent);
    }

    public function testLoggerHandlerIsSet()
    {
        $options = new ClientOptions();
        $options->setLogger($this->createMock(LoggerInterface::class));

        $stack = $this->extractStack($options);

        $handlerPresent = false;
        foreach ($stack as $handler) {
            if ($handler[1] === 'logger') {
                $handlerPresent = true;
                break;
            }
        }

        $this->assertTrue($handlerPresent);
    }

    public function testRateLimitHandlerIsNotSet()
    {
        $options = new ClientOptions();
        $options->setHandleRateLimit(false);

        $stack = $this->extractStack($options);

        $handlerPresent = false;
        foreach ($stack as $handler) {
            if ($handler[1] === 'rate_limit') {
                $handlerPresent = true;
                break;
            }
        }

        $this->assertFalse($handlerPresent);
    }

    public function testCreateExceptionCallback()
    {
        $testOk = false;

        $response = $this->createMock(ResponseInterface::class);
        $response
            ->method('getBody')
            ->willReturn('{"foo": "bar"}');

        $exception = $this->createMock(GuzzleHttp\Exception\RequestException::class);
        $exception
            ->method('hasResponse')
            ->willReturn(true);
        $exception
            ->method('getResponse')
            ->willReturn($response);

        /** @var Guzzle6Adapter|\PHPUnit_Framework_MockObject_MockObject $instance */
        $instance  = new Guzzle6Adapter();
        $reflexion = new \ReflectionClass($instance);
        $method    = $reflexion->getMethod('createExceptionCallback');
        $method->setAccessible(true);

        $errorCallback = $method->invoke($instance,
            function (HalResource $resource) use (&$testOk) {
                $testOk = true;
            }
        );
        $errorCallback($exception);

        $this->assertTrue($testOk);
    }

    /**
     * Build adapter from option and extract handle stack
     *
     * @param ClientOptions $options
     *
     * @return mixed
     * @throws \ReflectionException
     */
    private function extractStack(ClientOptions $options)
    {
        $client = $this->createMock(GuzzleHttp\Client::class);

        /** @var Guzzle6Adapter|\PHPUnit_Framework_MockObject_MockObject $instance */
        $instance = new Guzzle6Adapter($options);

        $reflexion = new \ReflectionClass($instance);

        // Override http client with mock
        $property = $reflexion->getProperty('client');
        $property->setAccessible(true);
        $property->setValue($instance, $client);

        // Extract HandlerStack
        $propertyRes = $reflexion->getProperty('stack');
        $propertyRes->setAccessible(true);

        /** @var HandlerStack $handlerStack */
        $handlerStack = $propertyRes->getValue($instance);

        $reflexionRes = new \ReflectionClass($handlerStack);
        $propertyRes  = $reflexionRes->getProperty('stack');
        $propertyRes->setAccessible(true);

        // Extract stack from handler stack
        return $propertyRes->getValue($handlerStack);
    }
}

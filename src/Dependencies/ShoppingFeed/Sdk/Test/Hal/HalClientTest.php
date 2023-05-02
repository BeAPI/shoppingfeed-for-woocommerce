<?php
namespace ShoppingFeed\Sdk\Test\Hal;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message;
use ShoppingFeed\Sdk\Hal;
use ShoppingFeed\Sdk\Hal\HalResource;
use ShoppingFeed\Sdk\Http;

class HalClientTest extends TestCase
{
    public function testCreateRequest()
    {
        $params  = ['GET', '/test', ['FakeHeader' => 'HeaderValue'], 'test'];
        $request = $this->createMock(Message\RequestInterface::class);
        /** @var Http\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject $httpClient */
        $httpClient = $this->createMock(Http\Adapter\AdapterInterface::class);
        $httpClient
            ->expects($this->once())
            ->method('createRequest')
            ->with(...$params)
            ->willReturn($request);

        $instance = new Hal\HalClient('http://fake.uri', $httpClient);
        $result   = $instance->createRequest(...$params);

        $this->assertSame($request, $result);
    }

    public function testRequest()
    {
        /** @var Http\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject $httpClient */
        $httpClient = $this->createMock(Http\Adapter\AdapterInterface::class);
        $httpClient
            ->expects($this->once())
            ->method('createRequest')
            ->willReturn(
                $this->createMock(Message\RequestInterface::class)
            );

        /** @var HalClient|\PHPUnit_Framework_MockObject_MockObject $instance */
        $instance = $this
            ->getMockBuilder(Hal\HalClient::class)
            ->setConstructorArgs(['http://fake.uri', $httpClient])
            ->setMethods(['send', 'createRequest'])
            ->getMock();

        $instance
            ->expects($this->once())
            ->method('send')
            ->willReturn(true);

        $this->assertTrue($instance->request('GET', '/test'));
    }

    public function testCreateResource()
    {
        /** @var Http\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject $httpClient */
        $httpClient = $this->createMock(Http\Adapter\AdapterInterface::class);
        $response   = $this->createMock(Message\ResponseInterface::class);
        $response
            ->expects($this->once())
            ->method('getBody')
            ->willReturn('{"foo":"bar", "foo2":"bar2"}');

        $instance = new Hal\HalClient('http://fake.uri', $httpClient);
        $resource = $instance->createResource($response);

        $this->assertInstanceOf(Hal\HalResource::class, $resource);
    }

    public function testWithToken()
    {
        /** @var Http\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject $httpClient */
        $httpClient = $this->createMock(Http\Adapter\AdapterInterface::class);
        $httpClient
            ->expects($this->once())
            ->method('withToken')
            ->with('3213213132132131')
            ->willReturn($this->createMock(Http\Adapter\AdapterInterface::class));

        $instance = new Hal\HalClient('http://fake.uri', $httpClient);
        $client   = $instance->withToken('3213213132132131');

        $this->assertInstanceOf(Hal\HalClient::class, $client);
        $this->assertNotSame($instance, $client);
    }

    /**
     * @throws \ReflectionException
     */
    public function testSend()
    {
        /** @var Http\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject $httpClient */
        $httpClient = $this->createMock(Http\Adapter\AdapterInterface::class);
        $httpClient
            ->expects($this->once())
            ->method('send')
            ->willReturn($this->createMock(Message\ResponseInterface::class));

        $clientInstanceMock = $this
            ->getMockBuilder(Hal\HalClient::class)
            ->setConstructorArgs(['http://fake.uri', $httpClient])
            ->setMethods(['createResource'])
            ->getMock();

        $clientInstanceMock
            ->expects($this->once())
            ->method('createResource')
            ->willReturn($this->createMock(Hal\HalResource::class));

        $clientInstanceMock->send($this->createMock(Message\RequestInterface::class));
    }

    /**
     * @throws \ReflectionException
     */
    public function testBatchSend()
    {
        /** @var Http\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject $httpClient */
        $httpClient = $this->createMock(Http\Adapter\AdapterInterface::class);
        $httpClient
            ->expects($this->once())
            ->method('batchSend')
            ->willReturn($this->createMock(Message\ResponseInterface::class));

        $instance = new Hal\HalClient('http://fake.uri', $httpClient);

        $instance->batchSend([$this->createMock(Message\RequestInterface::class)]);
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \ReflectionException
     */
    public function testSendNoResponse()
    {
        $httpClient = $this->createMock(Http\Adapter\AdapterInterface::class);
        $httpClient
            ->expects($this->once())
            ->method('send')
            ->willReturn(null);

        $clientInstance = new Hal\HalClient('http://fake.uri', $httpClient);

        $result = $clientInstance->send(
            $this->createMock(Message\RequestInterface::class)
        );
        $this->assertNull($result);
    }

    public function testSendWithEmptyResponseSkipEncoding()
    {
        $request = $this->createMock(Message\RequestInterface::class);

        $response = $this->createMock(Message\ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getBody')->willReturn('');

        $client = $this->createMock(Http\Adapter\AdapterInterface::class);
        $client
            ->expects($this->once())
            ->method('send')
            ->willReturn($response);

        $instance = new Hal\HalClient('http://fake.uri', $client);
        $result   = $instance->send($request);

        $this->assertSame([], $result->getProperties(), 'No properties are filled');
    }

    public function testItDecodeResponse()
    {
        $request = $this->createMock(Message\RequestInterface::class);

        $response = $this->createMock(Message\ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getBody')->willReturn('{"status":"ok"}');

        $client = $this->createMock(Http\Adapter\AdapterInterface::class);
        $client
            ->expects($this->once())
            ->method('send')
            ->willReturn($response);

        $instance = new Hal\HalClient('http://fake.uri', $client);
        $result   = $instance->send($request);

        $this->assertSame(['status' => 'ok'], $result->getProperties(), 'json has been decoded');
    }

    public function testGetAdapter()
    {
        $httpClient = $this->createMock(Http\Adapter\AdapterInterface::class);
        $instance   = new Hal\HalClient('http://fake.uri', $httpClient);

        $this->assertSame($httpClient, $instance->getAdapter());
    }
}

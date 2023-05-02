<?php
namespace ShoppingFeed\Sdk\Test\Hal;

use GuzzleHttp\Exception\RequestException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use ShoppingFeed\Sdk\Hal\HalClient;
use ShoppingFeed\Sdk\Hal\HalLink;
use ShoppingFeed\Sdk\Hal\HalResource;

class HalLinkTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|HalClient
     */
    private $client;

    public function setUp()
    {
        $this->client = $this->createMock(HalClient::class);
    }

    public function testSetters()
    {
        $instance = new HalLink(
            $this->client,
            'http://base.url',
            [
                'templated' => true,
                'type'      => 'Type',
                'name'      => 'Name',
                'title'     => 'Title',
            ]
        );

        $this->assertEquals('Type', $instance->getType());
        $this->assertEquals('Name', $instance->getName());
        $this->assertEquals('Title', $instance->getTitle());
        $this->assertEquals('http://base.url', $instance->getHref());
        $this->assertTrue($instance->isTemplated());
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testHttpClientCalls()
    {
        $this->client
            ->expects($this->exactly(5))
            ->method('send')
            ->willReturn(
                $this->createMock(HalResource::class)
            );

        $instance = $this
            ->getMockBuilder(HalLink::class)
            ->setConstructorArgs([$this->client, 'http://base.url'])
            ->setMethods(['createRequest'])
            ->getMock();

        $instance
            ->expects($this->exactly(5))
            ->method('createRequest')
            ->willReturn(
                $this->createMock(RequestInterface::class)
            );

        $instance->get();
        $instance->delete([]);
        $instance->put([]);
        $instance->patch([]);
        $instance->post([]);
    }

    public function testSend()
    {
        $this->client
            ->expects($this->once())
            ->method('send')
            ->willReturn(
                $this->createMock(HalResource::class)
            );

        $instance = $this
            ->getMockBuilder(HalLink::class)
            ->setConstructorArgs([$this->client, 'http://base.url'])
            ->setMethods(['createRequest'])
            ->getMock();

        $instance->send(
            $this->createMock(RequestInterface::class)
        );
    }

    public function testGetCreateRequest()
    {
        $this->client
            ->expects($this->once())
            ->method('createRequest')
            ->willReturn(
                $this->createMock(RequestInterface::class)
            );

        $instance = $this
            ->getMockBuilder(HalLink::class)
            ->setConstructorArgs([$this->client, 'http://base.url'])
            ->setMethods(['getUri'])
            ->getMock();

        $instance
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('/fake/uri');

        $instance->createRequest('GET');
    }

    public function testCreateRequestWithContent()
    {
        $uri = '/fake/uri';

        $this->client
            ->expects($this->exactly(4))
            ->method('createRequest')
            ->with(
                $this->isType('string'),
                $uri,
                ['Content-Type' => 'application/json'],
                $this->isType('string')
            )
            ->willReturn(
                $this->createMock(RequestInterface::class)
            );

        $instance = $this
            ->getMockBuilder(HalLink::class)
            ->setConstructorArgs([$this->client, 'http://base.url'])
            ->setMethods(['getUri'])
            ->getMock();

        $instance
            ->method('getUri')
            ->willReturn($uri);

        $instance->createRequest('POST', [], ['body' => ['id' => 123]]);
        $instance->createRequest('PUT', [], ['body' => ['id' => 123]]);
        $instance->createRequest('PATCH', [], ['body' => ['id' => 123]]);
        $instance->createRequest('DELETE', [], ['body' => ['id' => 123]]);
    }

    public function testCreateRequestWithoutContent()
    {
        $uri = '/fake/uri';

        $this->client
            ->method('createRequest')
            ->with(
                $this->isType('string'),
                $uri,
                []
            )
            ->willReturn(
                $this->createMock(RequestInterface::class)
            );

        $instance = $this
            ->getMockBuilder(HalLink::class)
            ->setConstructorArgs([$this->client, 'http://base.url'])
            ->setMethods(['getUri'])
            ->getMock();

        $instance
            ->method('getUri')
            ->willReturn($uri);

        $instance->createRequest('POST', [], null);
        $instance->createRequest('POST', [], '');
        $instance->createRequest('PUT', [], null);
        $instance->createRequest('PUT', [], '');
        $instance->createRequest('PATCH', [], null);
        $instance->createRequest('PATCH', [], '');
        $instance->createRequest('DELETE', [], null);
        $instance->createRequest('DELETE', [], '');
    }

    public function testBatchSend()
    {
        $request = ['request'];
        $this->client
            ->expects($this->once())
            ->method('batchSend')
            ->with(
                $this->callback(
                    function ($requests) use ($request) {
                        return $requests === $request;
                    }
                )
            );

        $instance = new HalLink($this->client, 'http://base.url');

        $instance->batchSend($request);
    }

    public function testBatchSendWithOption()
    {
        $request = ['request'];
        $options = ['test' => 'option'];
        $this->client
            ->expects($this->once())
            ->method('batchSend')
            ->with(
                $this->callback(
                    function ($requests) use ($request) {
                        return $requests === $request;
                    }
                ),
                $this->callback(
                    function ($config) use ($options) {
                        return $config['options'] === $options;
                    }
                )
            );

        $instance = new HalLink($this->client, 'http://base.url');

        $instance->batchSend($request, null, null, $options);
    }

    public function testBatchSendWithSuccessCallbacks()
    {
        $test    = $this;
        $request = ['request'];
        $success = function () {
            echo 'Success';
        };
        $this->client
            ->expects($this->once())
            ->method('batchSend')
            ->with(
                $this->callback(
                    function ($requests) use ($request) {
                        return $requests === $request;
                    }
                ),
                $this->callback(
                    function ($config) use ($test) {
                        $this->expectOutputString('Success');
                        $config['fulfilled']($test->createMock(ResponseInterface::class));
                        return true;
                    }
                )
            );

        $instance = new HalLink($this->client, 'http://base.url');

        $instance->batchSend($request, $success, null);
    }

    public function testBatchSendWithErrorCallback()
    {
        $test    = $this;
        $request = ['request'];
        $error   = function () {
            echo 'Error';
        };
        $this->client
            ->expects($this->once())
            ->method('batchSend')
            ->with(
                $this->callback(
                    function ($requests) use ($request) {
                        return $requests === $request;
                    }
                ),
                $this->callback(
                    function ($config) use ($test) {
                        $exception = $test->createMock(\Exception::class);
                        $config['rejected']($exception);
                        $this->expectOutputString('Error');
                        return true;
                    }
                )
            );

        $instance = new HalLink($this->client, 'http://base.url');

        $instance->batchSend($request, null, $error);
    }

    public function testGetUriDefault()
    {
        $instance = $this
            ->getMockBuilder(HalLink::class)
            ->setConstructorArgs([$this->client, 'http://base.url'])
            ->setMethods(['getHref'])
            ->getMock();

        $instance
            ->expects($this->once())
            ->method('getHref')
            ->willReturn('');

        $instance->getUri([]);
    }

    public function testGetUriTemplated()
    {
        $instance = new HalLink($this->client, 'http://base.url', ['templated' => true]);

        $this->assertInternalType('string', $instance->getUri([]));
    }

    /**
     * @dataProvider addedHrefProvider
     */
    public function testWithAddedHref($href, $added, $expected)
    {
        $instance = new HalLink($this->client, $href);
        $this->assertSame(
            $instance->withAddedHref($added)->getHref(),
            $expected
        );
    }

    public function addedHrefProvider()
    {
        return [
            ['http://base.url/v1', '12', 'http://base.url/v1/12'],
            ['http://base.url/v1/', '12', 'http://base.url/v1/12'],
            ['http://base.url/v1/', '/12', 'http://base.url/v1/12'],
            ['http://base.url/v1', '/12', 'http://base.url/v1/12'],
            ['http://base.url/v1', '/12/', 'http://base.url/v1/12/'],
        ];
    }
}

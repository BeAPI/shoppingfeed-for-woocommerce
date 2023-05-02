<?php
namespace ShoppingFeed\Sdk\Test\Client;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use ShoppingFeed\Sdk\Client\Client;
use ShoppingFeed\Sdk\Client\ClientOptions;

class ClientOptionsTest extends TestCase
{
    public function testGetterSetter()
    {
        $uri      = 'http://uri.com';
        $logger   = $this->createMock(LoggerInterface::class);
        $headers  = ['HeaderName' => 'HeaderValue'];
        $instance = new ClientOptions();
        $instance
            ->addHeaders($headers)
            ->setLogger($logger)
            ->setRetryOnServerError(5)
            ->setHandleRateLimit(true)
            ->setPlatform('MyPlatform', '1.1.4')
            ->setBaseUri($uri);

        $headers = [
            'HeaderName'      => 'HeaderValue',
            'Accept'          => 'application/json',
            'User-Agent'      => 'SF-SDK-PHP/' . Client::VERSION . ' (MyPlatform; 1.1.4)',
            'Accept-Encoding' => 'gzip',
        ];

        $this->assertSame($logger, $instance->getLogger());
        $this->assertEquals(5, $instance->getRetryOnServerError());
        $this->assertEquals($uri, $instance->getBaseUri());
        $this->assertEquals($headers, $instance->getHeaders());
        $this->assertTrue($instance->handleRateLimit());
    }
}

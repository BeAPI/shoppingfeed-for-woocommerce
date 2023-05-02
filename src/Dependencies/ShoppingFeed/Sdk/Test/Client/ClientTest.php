<?php
namespace ShoppingFeed\Sdk\Test\Client;

use PHPUnit\Framework\TestCase;
use ShoppingFeed\Sdk;

class ClientTest extends TestCase
{
    public function testCreateSession()
    {
        $sessionMock = $this->createMock(Sdk\Api\Session\SessionResource::class);
        $credential  = $this->createMock(Sdk\Credential\CredentialInterface::class);
        $credential
            ->expects($this->once())
            ->method('authenticate')
            ->willReturn($sessionMock);

        $instance = new Sdk\Client\Client();
        $session  = $instance::createSession($credential);

        $this->assertInstanceOf(Sdk\Api\Session\SessionResource::class, $session);
        $this->assertSame($sessionMock, $session);
    }

    public function testGetClient()
    {
        $instance = new Sdk\Client\Client();
        $this->assertInstanceOf(Sdk\Hal\HalClient::class, $instance->getHalClient());

    }

    public function testPing()
    {
        $halResource = $this->createMock(Sdk\Hal\HalResource::class);
        $halResource
            ->expects($this->once())
            ->method('getProperty')
            ->with('timestamp')
            ->willReturn(123456789);

        $halClient = $this->createMock(Sdk\Hal\HalClient::class);
        $halClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($halResource);

        $instanceMock = $this
            ->getMockBuilder(Sdk\Client\Client::class)
            ->setMethods(['getHalClient'])
            ->getMock();

        $instanceMock
            ->expects($this->once())
            ->method('getHalClient')
            ->willReturn($halClient);

        $this->assertTrue($instanceMock->ping());
    }

    public function testAuthenticate()
    {
        $credential = $this->createMock(Sdk\Credential\CredentialInterface::class);
        $instance   = new Sdk\Client\Client();

        $credential
            ->expects($this->once())
            ->method('authenticate');

        $instance->authenticate($credential);
    }
}

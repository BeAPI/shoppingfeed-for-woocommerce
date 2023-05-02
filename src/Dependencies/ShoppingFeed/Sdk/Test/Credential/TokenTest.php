<?php
namespace ShoppingFeed\Sdk\Test\Credential;

use PHPUnit\Framework\TestCase;
use ShoppingFeed\Sdk;

class TokenTest extends TestCase
{
    public function testAuthenticate()
    {
        $client = $this->createMock(Sdk\Hal\HalClient::class);
        $client
            ->expects($this->once())
            ->method('withToken')
            ->willReturn($client);

        $client
            ->expects($this->once())
            ->method('request')
            ->willReturn($this->createMock(Sdk\Hal\HalResource::class));

        $instance = new Sdk\Credential\Token('123456');
        $session = $instance->authenticate($client);

        $this->assertInstanceOf(Sdk\Api\Session\SessionResource::class, $session);
    }
}

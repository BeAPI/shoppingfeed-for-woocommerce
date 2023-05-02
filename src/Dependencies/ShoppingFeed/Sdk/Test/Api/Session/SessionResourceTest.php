<?php
namespace ShoppingFeed\Sdk\Test\Api\Session;

use ShoppingFeed\Sdk;

class SessionResourceTest extends Sdk\Test\Api\AbstractResourceTest
{
    /**
     * @var array
     */
    private $resources = [];

    public function setUp()
    {
        $this->props = [
            'login'    => 'username',
            'email'    => 'user@mail.com',
            'token'    => 'fd9cf7c178a1efd30bb1aad0e302abde',
            'language' => 'en_US',
            'roles'    => ['user'],
        ];

        $this->resources = [
            $this->createMock(Sdk\Hal\HalResource::class),
            $this->createMock(Sdk\Hal\HalResource::class),
            $this->createMock(Sdk\Hal\HalResource::class),
        ];
    }

    public function testPropertiesGetters()
    {
        $this->initHalResourceProperties();

        $instance = new Sdk\Api\Session\SessionResource($this->halResource);

        $this->assertEquals($this->props['email'], $instance->getEmail());
        $this->assertEquals($this->props['login'], $instance->getLogin());
        $this->assertEquals($this->props['token'], $instance->getToken());
        $this->assertEquals($this->props['roles'], $instance->getRoles());
        $this->assertEquals($this->props['language'], $instance->getLanguageTag());
    }

    public function testGetAccountIdWithMatchedOrNullAccount()
    {
        $halResource = $this->createMock(Sdk\Hal\HalResource::class);
        $halResource
            ->expects($this->exactly(2))
            ->method('getFirstResource')
            ->with('account')
            ->willReturnOnConsecutiveCalls(
                $halResource,
                null
            );

        $halResource
            ->expects($this->once())
            ->method('getProperty')
            ->with('id')
            ->willReturn(12);

        $instance = new Sdk\Api\Session\SessionResource($halResource);
        $this->assertSame(12, $instance->getId(), 'First call resource will return mock');
        $this->assertNull($instance->getId(), 'Second resource call return NULL');
    }

    public function testGetStores()
    {
        $halResource = $this->createMock(Sdk\Hal\HalResource::class);
        $halResource
            ->expects($this->once())
            ->method('getResources')
            ->with('store')
            ->willReturn($this->resources);

        $instance = new Sdk\Api\Session\SessionResource($halResource);
        $stores   = $instance->getStores();

        $this->assertInstanceOf(
            Sdk\Api\Store\StoreCollection::class,
            $stores
        );
    }

    public function testSelectStore()
    {
        $stores   = $this->createMock(Sdk\Api\Store\StoreCollection::class);
        $instance = $this
            ->getMockBuilder(Sdk\Api\Session\SessionResource::class)
            ->setConstructorArgs(
                [$this->createMock(Sdk\Hal\HalResource::class)]
            )
            ->setMethods(['getStores'])
            ->getMock();

        $instance
            ->expects($this->exactly(3))
            ->method('getStores')
            ->willReturn($stores);

        $stores
            ->expects($this->exactly(2))
            ->method('getById')
            ->willReturn($this->createMock(Sdk\Hal\HalResource::class));
        $stores
            ->expects($this->once())
            ->method('getByName')
            ->willReturn($this->createMock(Sdk\Hal\HalResource::class));

        $instance->selectStore(10);
        $instance->selectStore('10');
        $instance->selectStore('storeName');
    }

    public function testGetMainStore()
    {
        $halResource = $this->createMock(Sdk\Hal\HalResource::class);
        $halResource
            ->expects($this->once())
            ->method('getFirstResource')
            ->with('store')
            ->willReturn(
                $this->createMock(Sdk\Hal\HalResource::class)
            );

        $instance = new Sdk\Api\Session\SessionResource($halResource);

        $this->assertInstanceOf(Sdk\Api\Store\StoreResource::class, $instance->getMainStore());
    }

    public function testGetMainStoreNotFound()
    {
        $halResource = $this->createMock(Sdk\Hal\HalResource::class);
        $halResource
            ->expects($this->once())
            ->method('getFirstResource')
            ->with('store');

        $instance = new Sdk\Api\Session\SessionResource($halResource);

        $this->assertNull($instance->getMainStore());
    }
}

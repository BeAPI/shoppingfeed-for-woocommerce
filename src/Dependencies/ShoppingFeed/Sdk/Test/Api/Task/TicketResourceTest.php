<?php
namespace ShoppingFeed\Sdk\Test\Api\Task;

use ShoppingFeed\Sdk\Api\Task\TicketResource;
use ShoppingFeed\Sdk\Hal\HalLink;
use ShoppingFeed\Sdk\Hal\HalResource;
use ShoppingFeed\Sdk\Resource\PaginatedResourceCollection;
use ShoppingFeed\Sdk\Test\Api\AbstractResourceTest;

class TicketResourceTest extends AbstractResourceTest
{
    public function setUp()
    {
        $this->props = [
            'id'          => '123abc',
            'batchId'     => 'abc123456def789ghi',
            'state'       => 'succeed',
            'scheduledAt' => '2019-08-26T10:25:30+00:00',
            'startedAt'   => '2019-08-26T10:25:30+00:00',
            'finishedAt'  => '2019-08-26T12:01:25+00:00',
            'payload'     => [
                'channel' => 'Amazon'
            ]
        ];
    }

    public function testGetproperty()
    {
        $this->initHalResourceProperties();

        $instance = new TicketResource($this->halResource);

        $this->assertEquals($this->props['id'], $instance->getId());
        $this->assertEquals($this->props['batchId'], $instance->getBatchId());
        $this->assertEquals($this->props['state'], $instance->getStatus());
        $this->assertEquals($this->props['payload']['channel'], $instance->getPayload()['channel']);
        $this->assertEquals(new \DateTime($this->props['scheduledAt']), $instance->getScheduledAt());
        $this->assertEquals(new \DateTime($this->props['startedAt']), $instance->getStartedAt());
        $this->assertEquals(new \DateTime($this->props['finishedAt']), $instance->getFinishedAt());
    }

    public function testGetPayloadProperty()
    {
        $this->initHalResourceProperties();
        $instance = new TicketResource($this->halResource);
        $this->assertNull($instance->getPayloadProperty('toto'), 'property does not exists');
        $this->assertSame($this->props['payload']['channel'], $instance->getPayloadProperty('channel'));
    }
}

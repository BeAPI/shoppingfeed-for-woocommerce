<?php

namespace ShoppingFeed\Sdk\Api\Order;

class OrderItemCollectionTest extends \PHPUnit_Framework_TestCase
{
    private $items = [
        [
            'reference' => 'a',
            'quantity'  => 1,
            'price'     => 2,
            'taxAmount' => 7.99,
        ],
        [
            'reference' => 'b',
            'quantity'  => 2,
            'price'     => 3,
            'taxAmount' => 4.99,
        ]
    ];

    public function testCreateCollectionFromArrayOfRemoteProperties()
    {
        $instance = OrderItemCollection::fromProperties($this->items);

        self::assertInstanceOf(OrderItemCollection::class, $instance);
        $this->assertCount(2, $instance);

        $items = $instance->getIterator()->getArrayCopy();
        $this->assertContainsOnlyInstancesOf(OrderItem::class, $items);
    }

    public function testItemsAreWellConstructedFromArray()
    {
        $instance = OrderItemCollection::fromProperties($this->items);
        $items    = $instance->getIterator()->getArrayCopy();

        $firstOne = new OrderItem(
            $this->items[0]['reference'],
            $this->items[0]['quantity'],
            $this->items[0]['price'],
            $this->items[0]['taxAmount']
        );

        $this->assertEquals($items[0], $firstOne);

        $secondOne = new OrderItem(
            $this->items[1]['reference'],
            $this->items[1]['quantity'],
            $this->items[1]['price'],
            $this->items[1]['taxAmount']
        );

        $this->assertEquals($items[1], $secondOne);
    }

    public function testCollectionCanBeRevertedBackToArray()
    {
        $instance = OrderItemCollection::fromProperties($this->items);
        $this->assertEquals($this->items, $instance->toArray());
    }
}
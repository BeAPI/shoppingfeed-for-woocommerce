<?php

namespace ShoppingFeed\Sdk\Api\Order;

class OrderItemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OrderItem
     */
    private $instance;

    public function setUp()
    {
        $this->instance = new OrderItem('a', 2, 9.99, 7.99);
    }

    public function testGetReference()
    {
        $this->assertSame('a', $this->instance->getReference(), 'Reference is the first constructor arg');
    }

    public function testGetQuantity()
    {
        $this->assertSame(2, $this->instance->getQuantity(), 'Quantity is the second constructor arg');
    }

    public function testGetUnitPrice()
    {
        $this->assertSame(9.99, $this->instance->getUnitPrice(), 'Unit Price is the third constructor arg');
    }

    public function testGetTaxAmount()
    {
        $this->assertSame(7.99, $this->instance->getTaxAmount(), 'TaxAmount is the last constructor arg');
    }

    public function testGetTotalPriceWithComputeRowPrice()
    {
        $this->assertSame(19.98, $this->instance->getTotalPrice());
    }
}


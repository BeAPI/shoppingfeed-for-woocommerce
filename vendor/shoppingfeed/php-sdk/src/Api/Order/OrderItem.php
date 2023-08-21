<?php
namespace ShoppingFeed\Sdk\Api\Order;

/**
 * Class that represents the cart items of the order.
 */
class OrderItem
{
    /**
     * @var string
     */
    private $reference;

    /**
     * @var int
     */
    private $quantity;

    /**
     * @var float
     */
    private $unitPrice;

    /**
     * @var float
     */
    private $taxAmount;

    /**
     * @param string $reference The product's reference
     * @param int    $quantity  Number product's occurrence in cart
     * @param float  $price     The price for a single unit of the reference (not price * quantity)
     * @param float  $taxAmount The tax amount of the item price
     */
    public function __construct($reference, $quantity, $price, $taxAmount)
    {
        $this->reference = $reference;
        $this->quantity  = $quantity;
        $this->unitPrice = $price;
        $this->taxAmount = $taxAmount;
    }

    /**
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @return float
     */
    public function getUnitPrice()
    {
        return $this->unitPrice;
    }

    /**
     * @return float
     */
    public function getTaxAmount()
    {
        return $this->taxAmount;
    }

    /**
     * @return float|int
     */
    public function getTotalPrice()
    {
        return $this->getUnitPrice() * $this->getQuantity();
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'reference' => $this->getReference(),
            'quantity'  => $this->getQuantity(),
            'price'     => $this->getUnitPrice(),
            'taxAmount' => $this->getTaxAmount(),
        ];
    }
}

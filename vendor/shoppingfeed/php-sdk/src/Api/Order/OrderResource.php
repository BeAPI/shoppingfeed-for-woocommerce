<?php
namespace ShoppingFeed\Sdk\Api\Order;

use ShoppingFeed\Sdk\Api\Channel\ChannelResource;
use ShoppingFeed\Sdk\Resource\AbstractResource;

class OrderResource extends AbstractResource
{
    /**
     * @return int
     */
    public function getId()
    {
        return (int) $this->getProperty('id');
    }

    /**
     * @return string
     */
    public function getReference()
    {
        return (string) $this->getProperty('reference');
    }

    /**
     * @return null|string
     */
    public function getStoreReference()
    {
        return $this->getProperty('storeReference');
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return (string) $this->getProperty('status');
    }

    /**
     * @return null|\DateTimeImmutable
     */
    public function getAcknowledgedAt()
    {
        return $this->getPropertyDatetime('acknowledgedAt');
    }

    /**
     * @return null|\DateTimeImmutable
     */
    public function getUpdatedAt()
    {
        return $this->getPropertyDatetime('updatedAt');
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getCreatedAt()
    {
        return $this->getPropertyDatetime('createdAt');
    }

    /**
     * @return array
     */
    public function getShippingAddress()
    {
        return $this->getProperty('shippingAddress');
    }

    /**
     * @return array
     */
    public function getBillingAddress()
    {
        return $this->getProperty('billingAddress');
    }

    /**
     * @return array
     */
    public function getPaymentInformation()
    {
        return $this->getProperty('payment');
    }

    /**
     * @return array
     */
    public function getShipment()
    {
        return $this->getProperty('shipment');
    }

    /**
     * Fetch order items details
     * The resource has to be loaded to access to items collection
     *
     * @return OrderItemCollection|OrderItem[]
     */
    public function getItems()
    {
        return OrderItemCollection::fromProperties(
            $this->getProperty('items', true) ?: []
        );
    }

    /**
     * @return array
     */
    public function getItemsReferencesAliases()
    {
        return $this->getProperty('itemsReferencesAliases');
    }

    /**
     * @return ChannelResource A partial representation of the channel resource
     */
    public function getChannel()
    {
        return new ChannelResource(
            $this->resource->getFirstResource('channel')
        );
    }
}

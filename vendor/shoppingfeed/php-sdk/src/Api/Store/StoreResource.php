<?php
namespace ShoppingFeed\Sdk\Api\Store;

use ShoppingFeed\Sdk\Api\Catalog;
use ShoppingFeed\Sdk\Api\Task;
use ShoppingFeed\Sdk\Api\Order\OrderDomain;
use ShoppingFeed\Sdk\Resource\AbstractResource;

class StoreResource extends AbstractResource
{
    /**
     * @return string
     */
    public function getName()
    {
        return $this->getProperty('name');
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->getProperty('id');
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->getProperty('status') === 'active';
    }

    /**
     * @return string One of the following status:
     *  - active
     *  - demo
     *  - deleted
     *  - suspended
     */
    public function getStatus()
    {
        return $this->getProperty('status');
    }

    /**
     * @return string
     */
    public function getCountryCode()
    {
        return $this->getProperty('country');
    }

    /**
     * @return StoreChannelDomain
     */
    public function getChannelApi()
    {
        return new StoreChannelDomain(
            $this->resource->getLink('channel')
        );
    }

    /**
     * @return Catalog\InventoryDomain
     */
    public function getInventoryApi()
    {
        return new Catalog\InventoryDomain(
            $this->resource->getLink('inventory')
        );
    }

    /**
     * @return OrderDomain
     */
    public function getOrderApi()
    {
        return new OrderDomain(
            $this->resource->getLink('order')
        );
    }

    /**
     * @return Catalog\PricingDomain
     */
    public function getPricingApi()
    {
        return new Catalog\PricingDomain(
            $this->resource->getLink('pricing')
        );
    }

    /**
     * @return Task\TicketDomain
     */
    public function getTicketApi()
    {
        return new Task\TicketDomain(
            $this->resource->getLink('ticket')
        );
    }
}

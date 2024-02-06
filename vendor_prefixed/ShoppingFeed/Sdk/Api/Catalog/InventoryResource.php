<?php
namespace ShoppingFeed\ShoppingFeedWC\Dependencies\ShoppingFeed\Sdk\Api\Catalog;

use ShoppingFeed\ShoppingFeedWC\Dependencies\ShoppingFeed\Sdk\Resource\AbstractResource;

class InventoryResource extends AbstractResource
{
    /**
     * @return int
     */
    public function getId()
    {
        return $this->getProperty('id');
    }

    /**
     * @return string
     */
    public function getReference()
    {
        return $this->getProperty('reference');
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->getProperty('quantity');
    }

    /**
     * @return \DateTimeImmutable|null
     *
     * @throws \Exception
     */
    public function getUpdatedAt()
    {
        return $this->getPropertyDatetime('updatedAt');
    }
}

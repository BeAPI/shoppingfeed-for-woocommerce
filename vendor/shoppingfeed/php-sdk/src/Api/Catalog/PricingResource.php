<?php
namespace ShoppingFeed\Sdk\Api\Catalog;

use ShoppingFeed\Sdk\Resource\AbstractResource;

class PricingResource extends AbstractResource
{
    /**
     * @return int
     */
    public function getId()
    {
        return $this->getProperty('id');
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->getProperty('price');
    }

    /**
     * @return int
     */
    public function getReference()
    {
        return $this->getProperty('reference');
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

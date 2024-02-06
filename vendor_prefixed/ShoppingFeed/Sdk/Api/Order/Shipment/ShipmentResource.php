<?php

namespace ShoppingFeed\ShoppingFeedWC\Dependencies\ShoppingFeed\Sdk\Api\Order\Shipment;

use ShoppingFeed\ShoppingFeedWC\Dependencies\ShoppingFeed\Sdk\Resource\AbstractResource;

class ShipmentResource extends AbstractResource
{
    public function getCarrier(): string
    {
        return (string) $this->getProperty('carrier');
    }

    public function getTrackingNumber(): string
    {
        return (string) $this->getProperty('trackingNumber');
    }

    public function getTrackingUrl(): string
    {
        return (string) $this->getProperty('trackingLink');
    }

    public function getReturnInfo(): array
    {
        return (array) $this->getProperty('returnInfo');
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->getPropertyDatetime('createdAt');
    }

    public function getItems(): ?array
    {
        return $this->getProperty('items');
    }
}

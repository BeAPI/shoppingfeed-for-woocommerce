<?php
namespace ShoppingFeed\ShoppingFeedWC\Dependencies\ShoppingFeed\Sdk\Api\Order;

use ShoppingFeed\ShoppingFeedWC\Dependencies\ShoppingFeed\Sdk\Api\Order\Shipment\ShipmentDomain;
use ShoppingFeed\ShoppingFeedWC\Dependencies\ShoppingFeed\Sdk\Api\Order\Shipment\ShipmentResource;
use ShoppingFeed\ShoppingFeedWC\Dependencies\ShoppingFeed\Sdk\Resource;

/**
 * @method OrderResource[] getIterator()
 * @method OrderResource[] getAll($criteria = [])
 * @method OrderResource[] getPage(array $criteria = [])
 * @method OrderResource[] getPages(array $criteria = [])
 * @method OrderResource getOne($identity)
 */
class OrderDomain extends Resource\AbstractDomainResource
{
    /**
     * @var string
     */
    protected $resourceClass = OrderResource::class;

    /** @return Resource\PaginatedResourceIterator<ShipmentResource> */
    public function getShipmentsByOrder(int $orderId)
    {
        return (new ShipmentDomain($this->link->withAddedHref($orderId . '/shipment')))->getAll();
    }

    /**
     * @param OrderOperation $operation
     *
     * @return OrderOperationResult
     */
    public function execute(OrderOperation $operation): OrderOperationResult
    {
        return $operation->execute($this->link);
    }
}

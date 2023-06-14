<?php
namespace ShoppingFeed\ShoppingFeedWC\Dependencies\ShoppingFeed\Sdk\Api\Order;

use ShoppingFeed\ShoppingFeedWC\Dependencies\ShoppingFeed\Sdk\Resource\AbstractDomainResource;

/**
 * @method OrderResource[] getIterator()
 * @method OrderResource[] getAll($criteria = [])
 * @method OrderResource[] getPage(array $criteria = [])
 * @method OrderResource[] getPages(array $criteria = [])
 * @method OrderResource getOne($identity)
 */
class OrderDomain extends AbstractDomainResource
{
    /**
     * @var string
     */
    protected $resourceClass = OrderResource::class;

    /**
     * @param OrderOperation $operation
     *
     * @return OrderOperationResult
     */
    public function execute(OrderOperation $operation)
    {
        return $operation->execute($this->link);
    }
}

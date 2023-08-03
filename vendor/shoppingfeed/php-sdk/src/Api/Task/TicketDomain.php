<?php
namespace ShoppingFeed\Sdk\Api\Task;

use ShoppingFeed\Sdk\Resource;

/**
 * @method TicketResource[] getIterator()
 * @method TicketResource[] getAll($page = 1, $limit = 100)
 * @method TicketResource getOne($identity)
 */
class TicketDomain extends Resource\AbstractDomainResource
{
    protected $resourceClass = TicketResource::class;

    protected $iteratorClass = TicketIterator::class;

    /**
     * @param string $batchId Filter tickets by related batch
     * @param array  $filters
     *
     * @return TicketResource[]|TicketIterator|\Traversable
     */
    public function getByBatch($batchId, array $filters = [])
    {
        $filters['batchId'] = (string) $batchId;

        return $this->createIterator(
            new Resource\PaginationCriteria(compact('filters'))
        );
    }
}

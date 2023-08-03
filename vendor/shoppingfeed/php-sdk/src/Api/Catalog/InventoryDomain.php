<?php
namespace ShoppingFeed\Sdk\Api\Catalog;

use ShoppingFeed\Sdk\Resource\AbstractDomainResource;

/**
 * @method InventoryResource[] getIterator()
 * @method InventoryResource[] getAll($page = 1, $limit = 100)
 * @method InventoryResource getOne($identity)
 */
class InventoryDomain extends AbstractDomainResource
{
    /**
     * @var string
     */
    protected $resourceClass = InventoryResource::class;

    /**
     * @param string $reference the resource reference
     *
     * @return null|InventoryResource
     */
    public function getByReference($reference)
    {
        $resource = $this->link->get([], ['query' => ['reference' => $reference]]);
        if ($resource && $resource->getProperty('count') > 0) {
            return new InventoryResource(
                $resource->getFirstResource('inventory'),
                false
            );
        }

        return null;
    }

    /**
     * Execute requested update
     *
     * @param InventoryUpdate $operation
     *
     * @return InventoryCollection
     */
    public function execute(InventoryUpdate $operation)
    {
        return $operation->execute($this->link);
    }
}

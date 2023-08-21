<?php
namespace ShoppingFeed\Sdk\Api\Catalog;

use ShoppingFeed\Sdk\Resource\AbstractDomainResource;

/**
 * @method PricingResource[] getIterator()
 * @method PricingResource[] getAll($page = 1, $limit = 100)
 * @method PricingResource   getOne($identity)
 */
class PricingDomain extends AbstractDomainResource
{
    /**
     * @var string
     */
    protected $resourceClass = PricingResource::class;

    /**
     * @param string $reference the resource reference
     *
     * @return null|PricingResource
     */
    public function getByReference($reference)
    {
        $resource = $this->link->get([], ['query' => ['reference' => $reference]]);
        if ($resource && $resource->getProperty('count') > 0) {
            return new PricingResource(
                $resource->getFirstResource('pricing'),
                false
            );
        }

        return null;
    }

    /**
     * Execute requested update
     *
     * @param PricingUpdate $operation
     *
     * @return PricingCollection
     */
    public function execute(PricingUpdate $operation)
    {
        return $operation->execute($this->link);
    }
}

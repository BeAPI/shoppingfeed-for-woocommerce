<?php
namespace ShoppingFeed\Sdk\Api\Catalog;

use ShoppingFeed\Sdk\Hal;
use ShoppingFeed\Sdk\Operation\AbstractBulkOperation;

class PricingUpdate extends AbstractBulkOperation
{
    /**
     * @param array|\Traversable $operations Optional iterable collection composed of:
     *                                       - key (string) : product's reference
     *                                       - value (int)  : product's new price
     */
    public function __construct($operations = [])
    {
        foreach ($operations as $reference => $price) {
            $this->add($reference, $price);
        }
    }

    /**
     * @param string $reference The product's reference
     * @param float  $price     The new product's price
     *
     * @return $this
     */
    public function add($reference, $price)
    {
        $reference = trim($reference);
        $price     = (float) $price;

        $this->operations[$reference] = compact('reference', 'price');

        return $this;
    }

    /**
     * @param Hal\HalLink $link
     *
     * @return PricingCollection
     */
    public function execute(Hal\HalLink $link)
    {
        // Create requests per batch
        $requests = [];
        $this->eachBatch(
            $this->createBatchProcessorCallback($link, $requests)
        );

        // Send requests
        $resources = [];
        $link->batchSend(
            $requests,
            $this->createSuccessCallback($resources),
            null,
            [],
            $this->getPoolSize()
        );

        return new PricingCollection($resources);
    }

    /**
     * Create batch processor
     *
     * @param Hal\HalLink $link
     * @param array       $requests
     *
     * @return \Closure
     */
    private function createBatchProcessorCallback(Hal\HalLink $link, array &$requests)
    {
        return function (array $chunk) use ($link, &$requests) {
            $requests[] = $link->createRequest('PUT', [], ['pricing' => $chunk]);
        };
    }

    /**
     * Create success callback
     *
     * @param array $resources
     *
     * @return \Closure
     */
    private function createSuccessCallback(array &$resources)
    {
        return function (Hal\HalResource $resource) use (&$resources) {
            $pricings = $resource->getResources('pricing');
            if (count($pricings) > 0) {
                array_push($resources, ...$pricings);
            }
        };
    }
}

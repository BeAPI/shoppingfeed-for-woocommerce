<?php
namespace ShoppingFeed\Sdk\Api\Catalog;

use ShoppingFeed\Sdk\Hal;
use ShoppingFeed\Sdk\Operation\AbstractBulkOperation;

class InventoryUpdate extends AbstractBulkOperation
{
    /**
     * @param array|\Traversable $operations Optional iterable collection composed of:
     *                                       - key (string) : product's reference
     *                                       - value (int)  : product's new quantity
     */
    public function __construct($operations = [])
    {
        foreach ($operations as $reference => $quantity) {
            $this->add($reference, $quantity);
        }
    }

    /**
     * @param string $reference The product's reference
     * @param int    $quantity  The new product's quantity
     *
     * @return $this
     */
    public function add($reference, $quantity)
    {
        $reference = trim($reference);
        $quantity  = max(0, $quantity);

        $this->operations[$reference] = compact('reference', 'quantity');

        return $this;
    }

    /**
     * @param Hal\HalLink $link
     *
     * @return InventoryCollection
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

        return new InventoryCollection($resources);
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
            $requests[] = $link->createRequest('PUT', [], ['inventory' => $chunk]);
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
            $inventory = $resource->getResources('inventory');
            if (count($inventory) > 0) {
                array_push($resources, ...$inventory);
            }
        };
    }
}

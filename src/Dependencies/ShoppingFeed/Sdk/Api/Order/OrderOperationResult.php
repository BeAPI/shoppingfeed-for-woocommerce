<?php
namespace ShoppingFeed\ShoppingFeedWC\Dependencies\ShoppingFeed\Sdk\Api\Order;

use ShoppingFeed\ShoppingFeedWC\Dependencies\ShoppingFeed\Sdk\Api\Task;

class OrderOperationResult
{
    /**
     * @var Task\TicketDomain[]
     */
    private $batches;

    public function __construct(array $resources = [])
    {
        $this->setBatches($resources);
    }

    /**
     * Get the iterator for all tickets generated by the operation
     *
     * @return Task\TicketResource[]|\Traversable
     */
    public function getTickets()
    {
        foreach ($this->batches as $id => $domain) {
            foreach ($domain->getByBatch($id) as $ticket) {
                yield $ticket;
            }
        }
    }

    /**
     * @return string[] The list of ticket batch ids generated by the operation
     */
    public function getBatchIds()
    {
        return array_keys($this->batches);
    }

    /**
     * Wait for all tickets to be processed.
     *
     * @param int $timeout   Seconds to wait for each batch until stop
     * @param int $sleepSecs Seconds to wait between to calls
     *
     * @return $this                      The current instance
     */
    public function wait($timeout = null, $sleepSecs = 1)
    {
        foreach ($this->batches as $id => $domain) {
            $domain->getByBatch($id)->wait($timeout, $sleepSecs);
        }

        return $this;
    }

    /**
     * @var \ShoppingFeed\ShoppingFeedWC\Dependencies\ShoppingFeed\Sdk\Hal\HalResource[] $resources
     */
    private function setBatches(array $resources)
    {
        $this->batches = [];
        foreach ($resources as $resource) {
            // Stored element is the domain in order to avoid early api calls
            $batchId = $resource->getProperty('id');
            $domain  = new Task\TicketDomain($resource->getLink('ticket'));

            $this->batches[$batchId] = $domain;
        }
    }
}

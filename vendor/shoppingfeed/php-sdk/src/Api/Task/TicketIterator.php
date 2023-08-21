<?php
namespace ShoppingFeed\Sdk\Api\Task;

use ShoppingFeed\Sdk\Exception;
use ShoppingFeed\Sdk\Resource\PaginatedResourceIterator;

class TicketIterator extends PaginatedResourceIterator
{
    /**
     * Wait for all related ticket in collection to be processed.
     * The SDK will try a new call ev$sleepSec interval forever or until the defined timeout is reached.
     *
     * @param int $timeout Recommended to be defined, seconds to wait before fails
     * @param int $sleepSec The number of seconds to wait between two calls
     *
     * @return $this The new instance of TicketIterator with up-to-date tickets
     */
    public function wait($timeout = null, $sleepSec = 1)
    {
        $until    = null;
        $timeout  = (int) $timeout;
        $instance = $this;
        $sleepSec = (int) $sleepSec;

        if ($timeout > 0) {
            $until = time() + (int) $timeout;
        }

        while ($instance->isBeingProcessed()) {
            if (null !== $until && time() >= $until) {
                throw new Exception\RuntimeException(
                    sprintf('Process timed out after %d seconds', $timeout)
                );
            }

            $instance = $this->refresh();
            usleep($sleepSec * 1000000);
        }

        return $instance;
    }

    /**
     * Determine if at least one ticket in paginated collection is still being processed
     *
     * @return bool
     */
    public function isBeingProcessed()
    {
        return (bool) $this->getMeta('processing');
    }
}

<?php
namespace ShoppingFeed\ShoppingFeedWC\Dependencies\ShoppingFeed\Sdk\Resource;

/**
 * Iterates over a paginated collection until the last page has been reached
 */
class PaginatedResourceIterator implements \IteratorAggregate, \Countable
{
    /**
     * @var PaginatedResourceCollection
     */
    protected $paginator;

    /**
     * @param PaginatedResourceCollection $resource
     */
    public function __construct(PaginatedResourceCollection $resource)
    {
        $this->paginator = $resource;
    }

    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        $resource = $this->paginator;
        while (null !== $resource) {
            foreach ($resource->getIterator() as $item) {
                yield $item;
            }

            $resource = $resource->next();
        }
    }

    #[\ReturnTypeWillChange]
    public function count()
    {
        return $this->paginator->getTotalCount();
    }

    public function getMeta($name = null)
    {
        return $this->paginator->getMeta($name);
    }

    public function refresh()
    {
        $instance            = clone $this;
        $instance->paginator = $instance->paginator->refresh();

        return $instance;
    }
}

<?php
namespace ShoppingFeed\Sdk\Resource;

use ShoppingFeed\Sdk\Hal;

class PaginatedResourceCollection extends AbstractResource implements \IteratorAggregate, \Countable
{
    /**
     * @var string
     */
    private $resourceClass;

    /**
     * @param Hal\HalResource $resource
     * @param string          $resourceClass
     */
    public function __construct(Hal\HalResource $resource, $resourceClass)
    {
        $this->resourceClass = (string) $resourceClass;

        parent::__construct($resource, false);
    }

    /**
     * @return int
     */
    public function getTotalCount()
    {
        return (int) $this->getProperty('total');
    }

    /**
     * @return int
     */
    public function getCurrentCount()
    {
        return (int) $this->getProperty('count');
    }

    /**
     * @return int
     */
    public function getTotalPages()
    {
        return (int) $this->getProperty('pages');
    }

    /**
     * @return int
     */
    public function getCurrentPage()
    {
        return (int) $this->getProperty('page');
    }

    /**
     * @param null $key If defined, then look for a specific meta
     *
     * @return mixed
     */
    public function getMeta($key = null)
    {
        $all = $this->getProperty('meta');
        if (null === $key) {
            return $all;
        }

        if (isset($all[$key])) {
            return $all[$key];
        }

        return null;
    }

    /**
     * @return null|PaginatedResourceCollection
     */
    public function next()
    {
        $link = $this->resource->getLink('next');
        if (! $link) {
            return null;
        }

        $resource = $link->get();
        if (! $resource) {
            return null;
        }

        return new static($resource, $this->resourceClass);
    }

    /**
     * @inheritdoc
     */
    public function getIterator()
    {
        $data = current($this->resource->getAllResources()) ?: [];
        foreach ($data as $item) {
            yield new $this->resourceClass($item);
        }
    }

    /**
     * @inheritdoc
     */
    public function count()
    {
        return $this->getCurrentCount();
    }
}

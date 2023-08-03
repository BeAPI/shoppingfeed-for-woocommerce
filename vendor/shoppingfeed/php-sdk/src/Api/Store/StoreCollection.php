<?php
namespace ShoppingFeed\Sdk\Api\Store;

use ShoppingFeed\Sdk\Resource\AbstractCollection;

/**
 * @property StoreResource[] $resources
 */
class StoreCollection extends AbstractCollection
{
    protected $resourceClass = StoreResource::class;

    /**
     * @param string $name
     *
     * @return null|StoreResource
     */
    public function getByName($name)
    {
        foreach ($this->resources as $resource) {
            if ($resource->propertyMatch('name', $name)) {
                return $resource;
            }
        }

        return null;
    }

    /**
     * @param int $id
     *
     * @return null|StoreResource
     */
    public function getById($id)
    {
        foreach ($this->resources as $resource) {
            if ($resource->propertyMatch('id', $id)) {
                return $resource;
            }
        }

        return null;
    }

    /**
     * @param $idOrName
     *
     * @return null|StoreResource
     */
    public function select($idOrName)
    {
        if (is_int($idOrName) | ctype_digit($idOrName)) {
            return $this->getById($idOrName);
        }

        return $this->getByName($idOrName);
    }
}

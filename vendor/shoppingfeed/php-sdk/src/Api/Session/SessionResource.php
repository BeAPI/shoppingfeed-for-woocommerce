<?php
namespace ShoppingFeed\Sdk\Api\Session;

use ShoppingFeed\Sdk\Resource\AbstractResource;
use ShoppingFeed\Sdk\Api\Store;

class SessionResource extends AbstractResource
{
    /**
     * @return int|null NULL when account id is not found, id integer otherwise
     */
    public function getId()
    {
        if ($account = $this->resource->getFirstResource('account')) {
            return $account->getProperty('id');
        }

        return null;
    }

    /**
     * @return array A list of named roles
     */
    public function getRoles()
    {
        return $this->getProperty('roles') ?: [];
    }

    /**
     * @return string
     */
    public function getLogin()
    {
        return (string) $this->resource->getProperty('login');
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return (string) $this->resource->getProperty('email');
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return (string) $this->resource->getProperty('token');
    }

    /**
     * @return Store\StoreCollection|Store\StoreResource[]
     */
    public function getStores()
    {
        return new Store\StoreCollection(
            $this->resource->getResources('store')
        );
    }

    /**
     * Return the language tag, as following:
     *
     * - en_US
     * - fr_FR
     *
     * ...etc
     *
     * @return string
     */
    public function getLanguageTag()
    {
        return (string) $this->resource->getProperty('language');
    }

    /**
     * @param int|string $idOrName
     *
     * @return \ShoppingFeed\Sdk\Api\Store\StoreResource
     */
    public function selectStore($idOrName)
    {
        $stores = $this->getStores();

        if (is_int($idOrName) || ctype_digit($idOrName)) {
            return $stores->getById($idOrName);
        }

        return $stores->getByName($idOrName);
    }

    /**
     * @return \ShoppingFeed\Sdk\Api\Store\StoreResource|null
     */
    public function getMainStore()
    {
        $resource = $this->resource->getFirstResource('store');
        if ($resource) {
            return new Store\StoreResource($resource, true);
        }

        return null;
    }
}

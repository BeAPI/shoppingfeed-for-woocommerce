<?php
namespace ShoppingFeed\Sdk\Api\Store;

use ShoppingFeed\Sdk\Resource;

/**
 * @method StoreChannelResource[] getIterator()
 * @method StoreChannelResource[] getAll($criteria = [])
 * @method StoreChannelResource[] getPage(array $criteria = [])
 * @method StoreChannelResource[] getPages(array $criteria = [])
 * @method StoreChannelResource getOne($identity)
 */
class StoreChannelDomain extends Resource\AbstractDomainResource
{
    protected $resourceClass = StoreChannelResource::class;
}

<?php
namespace ShoppingFeed\Sdk\Test\Resource;

use ShoppingFeed\Sdk\Resource\AbstractCollection;

class CollectionMock extends AbstractCollection
{
    protected $resourceClass = ResourceMock::class;
}

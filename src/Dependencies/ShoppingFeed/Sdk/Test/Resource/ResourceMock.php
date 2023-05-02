<?php
namespace ShoppingFeed\Sdk\Test\Resource;

use ShoppingFeed\Sdk\Resource\AbstractResource;

/**
 * Class ResourceMock to be able to test some protected method of AbstractResource
 *
 * @package ShoppingFeed\Sdk\Test\Resource
 */
class ResourceMock extends AbstractResource
{
    public function getProperty($property, $initialize = false)
    {
        return parent::getProperty($property, $initialize);
    }

    public function propertyMatch($property, $value)
    {
        return parent::propertyMatch($property, $value);
    }

    public function getPropertyDatetime($property)
    {
        return parent::getPropertyDatetime($property);
    }

    public function initialize($force = false)
    {
        return parent::initialize($force);
    }

    public function isPartial()
    {
        return parent::isPartial();
    }
}

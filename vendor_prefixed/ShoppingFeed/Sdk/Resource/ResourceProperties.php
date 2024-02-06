<?php
namespace ShoppingFeed\ShoppingFeedWC\Dependencies\ShoppingFeed\Sdk\Resource;

use ShoppingFeed\ShoppingFeedWC\Dependencies\ShoppingFeed\Sdk\Exception;

/**
 * Resilient array access for resources array property
 */
class ResourceProperties implements \ArrayAccess
{
    private $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        if ($this->offsetExists($offset)) {
            return $this->data[$offset];
        }

        return null;
    }

    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        throw new Exception\RuntimeException('Resource properties cannot be modified');
    }

    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        throw new Exception\RuntimeException('Resource properties cannot be modified');
    }
}

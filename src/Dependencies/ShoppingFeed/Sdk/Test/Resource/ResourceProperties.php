<?php
namespace ShoppingFeed\Sdk\Resource;

use ShoppingFeed\Sdk\Exception;

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

    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    public function offsetGet($offset)
    {
        if ($this->offsetExists($offset)) {
            return $this->data[$offset];
        }

        return null;
    }

    public function offsetSet($offset, $value)
    {
        throw new Exception\RuntimeException('Resource properties cannot be modified');
    }

    public function offsetUnset($offset)
    {
        throw new Exception\RuntimeException('Resource properties cannot be modified');
    }
}

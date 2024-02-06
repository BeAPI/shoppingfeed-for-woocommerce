<?php
namespace ShoppingFeed\ShoppingFeedWC\Dependencies\ShoppingFeed\Sdk\Operation;

use ShoppingFeed\ShoppingFeedWC\Dependencies\ShoppingFeed\Sdk\Hal;

abstract class AbstractOperation
{
    /**
     * @param Hal\HalLink $link
     *
     * @return mixed
     */
    abstract public function execute(Hal\HalLink $link);
}

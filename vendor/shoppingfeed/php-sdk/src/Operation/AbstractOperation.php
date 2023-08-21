<?php
namespace ShoppingFeed\Sdk\Operation;

use ShoppingFeed\Sdk\Hal;

abstract class AbstractOperation
{
    /**
     * @param Hal\HalLink $link
     *
     * @return mixed
     */
    abstract public function execute(Hal\HalLink $link);
}

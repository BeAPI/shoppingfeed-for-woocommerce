<?php

declare(strict_types=1);

namespace ShoppingFeed\ShoppingFeedWC\Dependencies\GuzzleHttp\Promise;

/**
 * Interface used with classes that return a promise.
 */
interface PromisorInterface
{
    /**
     * Returns a promise.
     */
    public function promise(): PromiseInterface;
}

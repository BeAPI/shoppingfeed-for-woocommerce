<?php

namespace ShoppingFeed\ShoppingFeedWC\Dependencies\GuzzleHttp;

use ShoppingFeed\ShoppingFeedWC\Dependencies\Psr\Http\Message\MessageInterface;

interface BodySummarizerInterface
{
    /**
     * Returns a summarized message body.
     */
    public function summarize(MessageInterface $message): ?string;
}

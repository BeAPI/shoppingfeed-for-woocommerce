<?php

namespace ShoppingFeed\ShoppingFeedWC\Dependencies\ShoppingFeed\Sdk\Api\Order\Document;

abstract class AbstractDocument
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $type;

    public function __construct(
        string $path,
        string $type
    )
    {
        $this->path = $path;
        $this->type = $type;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getType(): string
    {
        return $this->type;
    }
}

<?php
namespace ShoppingFeed\Sdk\Api\Store;

use ShoppingFeed\Sdk\Api\Channel;
use ShoppingFeed\Sdk\Resource;

class StoreChannelResource extends Resource\AbstractResource
{
    private $channel;

    /**
     * The channel's id
     *
     * @return int
     */
    public function getId()
    {
        return $this->resource->getProperty('id');
    }

    /**
     * The channel's name
     *
     * @return string
     */
    public function getName()
    {
        return $this->getChannel()->getName();
    }

    /**
     * The channel's type
     *
     * @return string
     */
    public function getType()
    {
        return $this->getChannel()->getType();
    }

    /**
     * The channel's segment
     *
     * @return string
     */
    public function getSegment()
    {
        return $this->getChannel()->getSegment();
    }

    /**
     * Determine if the store is connected to the channel
     *
     * @return bool
     */
    public function isInstalled()
    {
        return $this->resource->getProperty('installed');
    }

    /**
     * Fetch the inner channel as resource
     *
     * @return Channel\ChannelResource
     */
    public function getChannel()
    {
        if (null === $this->channel) {
            $this->channel = new Channel\ChannelResource(
                $this->resource->getFirstResource('channel')
            );
        }

        return $this->channel;
    }
}

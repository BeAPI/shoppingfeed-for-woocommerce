<?php
namespace ShoppingFeed\Sdk\Api\Channel;

use ShoppingFeed\Sdk\Resource\AbstractResource;

class ChannelResource extends AbstractResource
{
    /**
     * The resource id
     *
     * @return int
     */
    public function getId()
    {
        return (int) $this->getProperty('id');
    }

    /**
     * The channel name
     *
     * @return string
     */
    public function getName()
    {
        return (string) $this->getProperty('name');
    }

    /**
     * @return string The channel's logo url
     */
    public function getLogoUrl()
    {
        return $this->resource->getLink('image')->getHref();
    }

    /**
     * @return string The channel type: marketplace, shopbot...etc
     */
    public function getType()
    {
        return (string) $this->getProperty('type', true);
    }

    /**
     * @return string The channel market segment position
     */
    public function getSegment()
    {
        return (string) $this->getProperty('segment', true);
    }

    /**
     * @return array A list of country codes where the channel is present
     */
    public function getCountryCodes()
    {
        return (array) $this->getProperty('countries', true);
    }
}

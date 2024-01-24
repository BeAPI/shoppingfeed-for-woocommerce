<?php
namespace ShoppingFeed\ShoppingFeedWC\Dependencies\ShoppingFeed\Sdk\Credential;

use ShoppingFeed\ShoppingFeedWC\Dependencies\ShoppingFeed\Sdk\Hal;

interface CredentialInterface
{
    /**
     * @param Hal\HalClient $client
     *
     * @return \ShoppingFeed\ShoppingFeedWC\Dependencies\ShoppingFeed\Sdk\Api\Session\SessionResource
     */
    public function authenticate(Hal\HalClient $client);
}

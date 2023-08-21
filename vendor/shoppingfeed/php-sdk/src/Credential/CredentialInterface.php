<?php
namespace ShoppingFeed\Sdk\Credential;

use ShoppingFeed\Sdk\Hal;

interface CredentialInterface
{
    /**
     * @param Hal\HalClient $client
     *
     * @return \ShoppingFeed\Sdk\Api\Session\SessionResource
     */
    public function authenticate(Hal\HalClient $client);
}

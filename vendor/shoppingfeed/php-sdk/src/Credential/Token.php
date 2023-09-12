<?php
namespace ShoppingFeed\Sdk\Credential;

use ShoppingFeed\Sdk\Hal;
use ShoppingFeed\Sdk\Api\Session\SessionResource;

class Token implements CredentialInterface
{
    /**
     * @var string
     */
    private $token;

    /**
     * @param string $token
     */
    public function __construct($token)
    {
        $this->token = trim($token);
    }

    /**
     * @inheritdoc
     */
    public function authenticate(Hal\HalClient $client)
    {
        $client = $client->withToken($this->token);

        return new SessionResource(
            $client->request('GET', 'v1/me'),
            false
        );
    }
}

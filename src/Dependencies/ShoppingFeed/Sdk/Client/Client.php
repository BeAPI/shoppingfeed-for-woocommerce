<?php
namespace ShoppingFeed\ShoppingFeedWC\Dependencies\ShoppingFeed\Sdk\Client;

use ShoppingFeed\ShoppingFeedWC\Dependencies\ShoppingFeed\Sdk\Credential\CredentialInterface;
use ShoppingFeed\ShoppingFeedWC\Dependencies\ShoppingFeed\Sdk\Hal;
use ShoppingFeed\ShoppingFeedWC\Dependencies\ShoppingFeed\Sdk\Http;

class Client
{
    const VERSION = '0.2.4';

    /**
     * @var Hal\HalClient
     */
    private $client;

    /**
     * @param CredentialInterface $credential
     * @param ClientOptions|null  $options
     *
     * @return \ShoppingFeed\ShoppingFeedWC\Dependencies\ShoppingFeed\Sdk\Api\Session\SessionResource
     */
    public static function createSession(CredentialInterface $credential, ClientOptions $options = null)
    {
        return (new self($options))->authenticate($credential);
    }

    /**
     * @param ClientOptions|null $options
     */
    public function __construct(ClientOptions $options = null)
    {
        if (null === $options) {
            $options = new ClientOptions();
        }

        if (null === $options->getHttpAdapter()) {
            $options->setHttpAdapter(new Http\Adapter\Guzzle6Adapter($options));
        }

        $this->client = new Hal\HalClient(
            $options->getBaseUri(),
            $options->getHttpAdapter()
        );
    }

    /**
     * @return Hal\HalClient
     */
    public function getHalClient()
    {
        return $this->client;
    }

    /**
     * Ping APi
     *
     * @return bool
     */
    public function ping()
    {
        return (bool) $this
            ->getHalClient()
            ->request('GET', 'v1/ping')
            ->getProperty('timestamp');
    }

    /**
     * @param CredentialInterface $credential
     *
     * @return \ShoppingFeed\ShoppingFeedWC\Dependencies\ShoppingFeed\Sdk\Api\Session\SessionResource
     */
    public function authenticate(CredentialInterface $credential)
    {
        return $credential->authenticate($this->client);
    }
}

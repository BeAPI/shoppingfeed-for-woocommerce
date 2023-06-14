<?php
namespace ShoppingFeed\ShoppingFeedWC\Dependencies\ShoppingFeed\Sdk\Hal;

use ShoppingFeed\ShoppingFeedWC\Dependencies\Psr\Http\Message\RequestInterface;
use ShoppingFeed\ShoppingFeedWC\Dependencies\Psr\Http\Message\ResponseInterface;
use ShoppingFeed\ShoppingFeedWC\Dependencies\ShoppingFeed\Sdk\Http\Adapter\AdapterInterface;
use ShoppingFeed\ShoppingFeedWC\Dependencies\ShoppingFeed\Sdk\Resource\Json;

class HalClient
{
    /**
     * @var AdapterInterface
     */
    private $client;

    /**
     * @var string
     */
    private $baseUri;

    /**
     * @param AdapterInterface $httpClient
     */
    public function __construct(
        $baseUri,
        AdapterInterface $httpClient
    )
    {
        $this->baseUri = $baseUri;
        $this->client  = $httpClient;
    }

    /**
     * @param $token
     *
     * @return HalClient
     */
    public function withToken($token)
    {
        $httpClient = $this->client->withToken($token);
        $instance   = new self($this->baseUri, $httpClient);

        return $instance;
    }

    /**
     * @param string      $method
     * @param string      $uri
     * @param array       $headers
     * @param null|string $body
     *
     * @return RequestInterface
     */
    public function createRequest($method, $uri, array $headers = [], $body = null)
    {
        return $this->client->createRequest($method, $uri, $headers, $body);
    }

    /**
     * @param $method
     * @param $uri
     * @param $options
     *
     * @return null|HalResource
     */
    public function request($method, $uri, array $options = [])
    {
        return $this->send(
            $this->client->createRequest($method, $uri),
            $options
        );
    }

    /**
     * @param       $requests
     * @param array $config
     *
     * @return void
     */
    public function batchSend($requests, array $config = [])
    {
        $this->client->batchSend($requests, $config);
    }

    /**
     * @param RequestInterface $request
     * @param array            $options
     *
     * @return null|HalResource
     */
    public function send(RequestInterface $request, array $options = [])
    {
        $response = $this->client->send($request, $options);
        if ($response instanceof ResponseInterface) {
            return $this->createResource($response);
        }

        return null;
    }

    /**
     * @param ResponseInterface $response
     *
     * @return HalResource
     */
    public function createResource(ResponseInterface $response)
    {
        $data = [];
        $body = trim($response->getBody());

        if ($body) {
            $data = Json::decode($body, true);
        }

        return HalResource::fromArray($this, $data);
    }

    /**
     * Get Http adapter
     *
     * @return AdapterInterface
     */
    public function getAdapter()
    {
        return $this->client;
    }
}

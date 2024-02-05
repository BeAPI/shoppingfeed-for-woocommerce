<?php
namespace ShoppingFeed\ShoppingFeedWC\Dependencies\ShoppingFeed\Sdk\Http\Adapter;

use ShoppingFeed\ShoppingFeedWC\Dependencies\Psr\Http\Message;
use ShoppingFeed\ShoppingFeedWC\Dependencies\ShoppingFeed\Sdk\Client\ClientOptions;

/**
 * Http Client Adapter Interface
 *
 * This interface ensure that any adapter that implements it will be compatible with the SDK functioning
 * Adapter should check for their dependency existence
 *
 * @package ShoppingFeed\ShoppingFeedWC\Dependencies\ShoppingFeed\Sdk\Http\Adapter
 */
interface AdapterInterface
{
    /**
     * Configure current adapter with given options
     *
     * @param ClientOptions $options
     *
     * @return AdapterInterface
     */
    public function configure(ClientOptions $options);

    /**
     * Send a single HTTP request
     *
     * @param Message\RequestInterface $request Psr\RequestInterface object ready to be sent
     * @param array                    $options Options to pass to the http client
     *
     * @return Message\ResponseInterface
     */
    public function send(Message\RequestInterface $request, array $options = []);

    /**
     * Send multiples HTTP requests
     *
     * @param array $requests An array of Psr\RequestInterface object ready to be sent
     * @param array $options  Options to pass to the http client
     *
     * @return void
     */
    public function batchSend(array $requests, array $options = []);

    /**
     * Create request from given parameters
     *
     * @param string $method  Http method
     * @param string $uri     The URI to call, ex: '/my/uri'
     * @param array  $headers An array of headers to add to the request, ex: array('MyHeader' => 'Its Content')
     * @param null   $body    The body as a string to send via the request
     *
     * @return Message\RequestInterface
     */
    public function createRequest($method, $uri, array $headers = [], $body = null);

    /**
     * Initiate request and get a response instance.
     *
     * @param string $method   Http method
     * @param string $uri      The URI to call, ex: '/my/uri'
     * @param array  $options  Options to pass to the http client
     *
     * @return Message\ResponseInterface
     */
    public function request($method, $uri, array $options = []);

    /**
     * Use the given token in the 'Authorization' header for all request sent via the adapter
     *
     * When the use perform authentication with the SDK, a new session is created, associated to this token.
     * As the SDK user can create more than one session with the same SDK client, the adapter must return
     * A copy or a new instance of the underlying HTTP client, which hold the session token.
     *
     * @param string $token The token associated to a new session
     *
     * @return AdapterInterface A unique copy of the the current instance that will use the token to perform HTTP calls
     */
    public function withToken($token);
}

<?php
namespace ShoppingFeed\ShoppingFeedWC\Dependencies\ShoppingFeed\Sdk\Hal;

use ShoppingFeed\ShoppingFeedWC\Dependencies\Psr\Http\Message\ResponseInterface;
use ShoppingFeed\ShoppingFeedWC\Dependencies\ShoppingFeed\Sdk\Http\UriTemplate;
use ShoppingFeed\ShoppingFeedWC\Dependencies\ShoppingFeed\Sdk\Resource\Json;

class HalLink
{
    /**
     * @var UriTemplate
     */
    private static $uriTemplate;

    /**
     * @var string
     */
    private $href;

    /**
     * @var bool
     */
    private $templated;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $title;

    /**
     * @var HalClient
     */
    private $client;

    /**
     * @param HalClient $client
     * @param           $href
     * @param array     $config
     */
    public function __construct(HalClient $client, $href, array $config = [])
    {
        $this->href   = $href;
        $this->client = $client;

        if (isset($config['templated'])) {
            $this->templated = (bool) $config['templated'];
        }

        if (isset($config['type'])) {
            $this->type = $config['type'];
        }

        if (isset($config['name'])) {
            $this->name = $config['name'];
        }

        if (isset($config['title'])) {
            $this->title = $config['title'];
        }
    }

    /**
     * @return string
     */
    public function getHref()
    {
        return $this->href;
    }

    /**
     * @return bool
     */
    public function isTemplated()
    {
        return $this->templated;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param $path
     * @param $variables
     *
     * @return HalLink
     */
    public function withAddedHref($path, $variables = [])
    {
        $instance       = clone $this;
        $instance->href = rtrim($instance->getUri($variables), '/') .
            '/' . ltrim($path, '/');

        return $instance;
    }

    /**
     * @param array $variables
     *
     * @return null|string|string[]
     */
    public function getUri(array $variables)
    {
        if (! $this->isTemplated()) {
            return $this->getHref();
        }

        if (null === static::$uriTemplate) {
            static::$uriTemplate = new UriTemplate();
        }

        return static::$uriTemplate->expand($this->getHref(), $variables);
    }

    /**
     * @param array $variables
     * @param array $options
     *
     * @return null|HalResource
     */
    public function get(array $variables = [], array $options = [])
    {
        return $this->client->send(
            $this->createRequest('GET', $variables),
            $options
        );
    }

    /**
     * @param mixed $data
     * @param array $variables
     * @param array $options
     *
     * @return null|HalResource
     */
    public function put($data, array $variables = [], array $options = [])
    {
        return $this->client->send(
            $this->createRequest('PUT', $variables, $data),
            $options
        );
    }

    /**
     * @param mixed $data
     * @param array $variables
     * @param array $options
     *
     * @return null|HalResource
     */
    public function patch($data, array $variables = [], array $options = [])
    {
        return $this->client->send(
            $this->createRequest('PATCH', $variables, $data),
            $options
        );
    }

    /**
     * @param mixed $data
     * @param array $variables
     * @param array $options
     *
     * @return null|HalResource
     */
    public function post($data, array $variables = [], array $options = [])
    {
        return $this->client->send(
            $this->createRequest('POST', $variables, $data),
            $options
        );
    }

    /**
     * @param mixed $data
     * @param array $variables
     * @param array $options
     *
     * @return null|HalResource
     */
    public function delete($data, array $variables = [], array $options = [])
    {
        return $this->client->send(
            $this->createRequest('DELETE', $variables, $data),
            $options
        );
    }

    /**
     * @param          $requests
     * @param callable $success
     * @param callable $error
     * @param array    $options
     * @param int      $concurrency
     *
     * @return void
     */
    public function batchSend(
        $requests,
        callable $success = null,
        callable $error = null,
        array $options = [],
        $concurrency = 10
    )
    {
        $config['concurrency'] = (int) $concurrency;
        $config['fulfilled']   = $this->createResponseCallback($success);
        $config['rejected']    = $this->createExceptionCallback($error);

        if ($options) {
            $config['options'] = $options;
        }

        $this->client->batchSend($requests, $config);
    }

    /**
     * @param \Psr\Http\Message\RequestInterface|\Psr\Http\Message\RequestInterface[] $request
     * @param array                               $config
     *
     * @return null|HalResource
     */
    public function send($request, array $config = [])
    {
        return $this->client->send($request, $config);
    }

    /**
     * @param string $method
     * @param array  $variables
     * @param mixed  $body
     *
     * @return \Psr\Http\Message\RequestInterface
     */
    public function createRequest($method, array $variables = [], $body = null, $headers = [])
    {
        $uri    = $this->getUri($variables);
        $method = strtoupper($method);

        $hasBody = null !== $body && '' !== $body;

        if ($hasBody && in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            if (! isset($headers['Content-Type'])) {
                $headers['Content-Type'] = 'application/json';
            }

            switch ($headers['Content-Type']) {
                case 'application/json':
                    $body = Json::encode($body);
                    break;
            }
        }

        return $this->client->createRequest($method, $uri, $headers, $body);
    }

    /**
     * @param callable|null $callback
     *
     * @return \Closure
     */
    private function createResponseCallback(callable $callback = null)
    {
        return function (ResponseInterface $response) use ($callback) {
            if ($callback) {
                $resource = $this->client->createResource($response);
                call_user_func($callback, $resource);
            }
        };
    }

    /**
     * @param callable|null $callback
     *
     * @return \Closure
     */
    private function createExceptionCallback(callable $callback = null)
    {
        return function (\Exception $exception) use ($callback) {
            call_user_func($callback, $exception);
        };
    }
}

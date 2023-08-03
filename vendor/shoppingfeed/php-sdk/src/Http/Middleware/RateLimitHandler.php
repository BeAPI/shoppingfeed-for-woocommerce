<?php
namespace ShoppingFeed\Sdk\Http\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * This handler manage API rate limits by mae the client entering to sleep,
 * Then retry the last query if the API returns a 429 response status code
 */
class RateLimitHandler
{
    /**
     * Retry 3 times the request before entering to error
     *
     * @var int
     */
    private $maxRetries;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param int                  $maxRetries
     * @param LoggerInterface|null $logger
     */
    public function __construct($maxRetries = 3, LoggerInterface $logger = null)
    {
        $this->maxRetries = (int) $maxRetries;
        $this->logger     = $logger;
    }

    /**
     * @param int                    $count
     * @param RequestInterface       $request
     * @param ResponseInterface|null $response
     *
     * @return bool
     */
    public function decide($count, RequestInterface $request, ResponseInterface $response = null)
    {
        if (! $response || $count >= $this->maxRetries) {
            return false;
        }

        return $response->getStatusCode() === 429;
    }

    /**
     * @param int               $count    Number of retries
     * @param ResponseInterface $response
     *
     * @return int milliseconds to wait before next call
     */
    public function delay($count, ResponseInterface $response)
    {
        $waitMS = (int) ceil($response->getHeaderLine('X-RateLimit-Wait') * 1000);
        if (null !== $this->logger) {
            $this->logger->notice(sprintf('Request throttled for %d ms', $waitMS));
        }

        return $waitMS;
    }
}

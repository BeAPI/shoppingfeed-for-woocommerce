<?php
namespace ShoppingFeed\Sdk\Http\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ServerErrorHandler
{
    const STATUS = [
        500 => true,
        502 => true,
        503 => true,
        504 => true,
    ];

    /**
     * Retry x times the request before entering to error
     *
     * @var int
     */
    private $maxRetries;

    public function __construct($maxRetries = 3)
    {
        $this->maxRetries = (int) $maxRetries;
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
        if ($count >= $this->maxRetries) {
            return false;
        }

        // Necessary to pass phpunit tests
        $status = self::STATUS;

        return ($response && isset($status[$response->getStatusCode()]));
    }
}

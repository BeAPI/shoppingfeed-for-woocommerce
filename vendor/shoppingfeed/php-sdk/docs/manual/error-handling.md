# Handle Rate-Limit and errors


### Rate-Limits

By default, the SDK gracefully handles a [429 response](https://httpstatuses.com/429) from the API : When occurred, the SDK waits (sleep) until new calls are available, then retries the last failed request.

The SDK request will fail if the API responds 3 times consecutively with a 429 response.

This feature can be disabled when creating an API client. If disabled, it will be done for all calls on any API point called with the client instance.

```php
<?php
namespace ShoppingFeed\Sdk;

$options = new Client\ClientOptions();
$options->setHandleRateLimit(false);
```

### Server errors

The SDK allows the possibility to retry requests when the server fails to respond, in the range of [5xx status codes](https://httpstatuses.com/). You can set how many times the request will be retried (with an exponential backoff)

```php
<?php
namespace ShoppingFeed\Sdk;

$options = new Client\ClientOptions();
$options->setRetryOnServerError(3);
```

### Debugging requests

If you want to trace the requests performed by the SDK, you can pass an instance of `Psr\LoggerInterface`, which will log all requests in `debug` level:

```php
<?php
namespace ShoppingFeed\Sdk;

$options = new Client\ClientOptions();
/** @var Psr\LoggerInterface $psrLogger */
$options->setLogger($psrLogger);
```

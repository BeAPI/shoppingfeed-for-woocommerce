# Authenticate and create SDK session

The SDK offers two ways to authenticate against the API:

### Login / password


```php
<?php
namespace ShoppingFeed\Sdk;

$credential = new Credential\Password('login', 'password');
$session    = Client\Client::createSession($credential);
```

By login, we understand your **account name**, not store name or email address.

This method costs 2 calls to the API:

- 1 for getting your API token based on login / password info
- 1 to get your session details


### Token

This is the preferred way, because this method only performs 1 HTTP call

```php
<?php
namespace ShoppingFeed\Sdk;

$credential = new Credential\Token('api_token');
$session    = Client\Client::createSession($credential);
```


### Client options

Optionally, you can pass options when creating a new session with the static method.

```php
<?php
namespace ShoppingFeed\Sdk;

$options = new Client\ClientOptions();
$options->setHandleRateLimit(false);
$options->setPlatform('Magento', '2.2.0');

$credential = new Credential\Token('api_token');
$session    = Client\Client::createSession($credential, $options);
```

Available options are:

| Name               | Description                                                                                                   |
|--------------------|---------------------------------------------------------------------------------------------------------------|
| platform   | Allow to add information regarding the platform and its version to user agent HTTP header sent by the sdk     |
| headers            | Allow to add headers to all HTTP calls made by the SDK                                                        |
| httpAdapter        | Allow to set your own HTTP adapter used by the SDK (cf. [http adapter doc](./../development/http-adapter.md)) |
| retryOnServerError | Allow to set the number of times the SDK should retry when a server error occurs                              |
| handleRateLimit    | Allow to enable/disable the retry of a call when server is overloaded                                         |
| logger             | Allow to pass your own logger, logger should be a Psr\LoggerInterface to be compatible with the client        |
| baseUri            | Allow to set the ShoppingFeed API baseUri to be used by the SDK                                               |

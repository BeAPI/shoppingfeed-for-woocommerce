Http Adapter
============

The shopping flux SDK comes with an adapter to use Guzzle6 Http client library.
But you can develop your own adapter if need be.

Integration
-----------
  
To use your own http client you just have to develop the proper adapter which should implement `Http\Adapter\AdapterInterface`.
Then declare it as an option you pass to the client :  

```php
$customerAdapter = new YourCustomAdapter();

$clientOptions = new \ShoppingFeed\Sdk\Client\ClientOptions();
$clientOptions->setHttpAdapter($customerAdapter);

$client  = new \ShoppingFeed\Sdk\Client\Client($clientOptions);
```

Adapter Creation
----------------

To develop your own adapter create a new class implementing `Http\Adapter\AdapterInterface` with these methods :

| Method          | Parameters                                                                                                           | Description |
|-----------------|----------------------------------------------------------------------------------------------------------------------|-------------|
| `send`          | <ul><li>`Psr\RequestInterface $request`</li><li>`array $options = []`</li></ul>                                      | Handle the http call of the given request. |
| `batchSend`     | <ul><li>`array $requests`</li><li>`array $options = []`</li></ul>                                                    | Send multiple requests |
| `createRequest` | <ul><li>`string $method`</li><li>`string $uri`</li><li>`array $headers = []`</li><li>`string $body = null`</li></ul> | Create a request from given parameters |
| `withToken`     | <ul><li>`string $token`</li><li>`$stack`</li></ul>                                                                   | Create a new instance of the adapter that use the token for identification |
| `configure`     | <ul><li>`ClientOptions $options`</li></ul>                                                                           | Allow to configure current adapter with new config object. This method should override any previous configuration set with the given one |


Batch Send
----------

Batch send method allow to handle sending of multiple requests.  
This method is in charge of splitting and regrouping request to optimise calls based on API limits.  
The `$config` parameter will contain these keys :

| Key            | Required | Type       | Description |
|----------------|----------|------------|-------------|
| `concurrency`  | yes      | `int`      | Handle the http call of the given request. |
| `fulfilled`    | yes      | `callable` | Callback that will handle all success responses. The callback take a `HalResource` build from the response |
| `rejected`     | yes      | `callable` | Callback that will handle all exception that occurred during the call. The callback take and `Exception` as a parameter. |
| `options`      | no       | `array`    | If additional options have been passed to the `batchSend()` method you will retrieve them here. |
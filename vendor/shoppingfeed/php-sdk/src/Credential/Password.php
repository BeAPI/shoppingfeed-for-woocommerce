<?php
namespace ShoppingFeed\Sdk\Credential;

use ShoppingFeed\Sdk\Hal;

class Password implements CredentialInterface
{
    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @param string $username
     * @param string $password
     */
    public function __construct($username, $password)
    {
        $this->username = trim($username);
        $this->password = trim($password);
    }

    /**
     * @inheritdoc
     */
    public function authenticate(Hal\HalClient $client)
    {
        $response = $client->request('POST', 'v1/auth', [
            'json' => [
                'grant_type' => 'password',
                'username'   => $this->username,
                'password'   => $this->password,
            ],
        ]);

        return $this->tokenizeResponse($response)->authenticate($client);
    }

    /**
     * @param Hal\HalResource $response
     *
     * @return Token
     */
    public function tokenizeResponse(Hal\HalResource $response)
    {
        return new Token($response->getProperty('access_token'));
    }
}

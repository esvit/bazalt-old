<?php

/**
 * Abstract class representing an authorization method
 */
abstract class Google_Auth
{
    const AUTH_SOURCE_NAME = 'Bazalt-Google_Auth-1.0.0';

    protected $authToken = null;

    /**
     * Constructs a new gapiAuthMethod class given an existing token
     *
     * @param String $authToken
     * @return gapiAuthMethod
     */
    public function __construct($authToken = null)
    {
        $this->authToken = $authToken;
    }

    /**
     * Return the auth token string retrieved from Google
     *
     * @return String
     */
    public function getToken()
    {
        return $this->authToken;
    }

    /**
     * Abstract method that returns the authorization method name
     *
     * @return String
     */
    protected abstract function getMethodName();

    /**
     * Abstract method that returns the identifier of the token in the auth header
     *
     * @return String
     */
    protected abstract function getTokenName();

    /**
     * Generate authorization token header for all requests
     *
     * @param String $token
     * @return Array
     */
    public function generateAuthHeader($token = null)
    {
        if ($token == null) {
            $token = $this->authToken;
        }
        return array(
            'Authorization' => $this->getMethodName() . ' ' . $this->getTokenName() . '=' . $token . ''
        );
    }

    /**
     * Parse the body of a returned key=value page
     *
     * @param String $content
     * @return Array
     */
    protected function parseBody($content)
    {
        // Convert newline delimited variables into url format then import to array
        parse_str(str_replace(array("\n", "\r\n"), '&', $content), $array);
        return $array;
    }
}
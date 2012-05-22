<?php

class Google_Auth_ClientLogin extends Google_Auth
{
    const REQUEST_URL = 'https://www.google.com/accounts/ClientLogin';

    /**
     * Authenticate and return a seeded gapi instance
     *
     * @param String $email
     * @param String $password
     * @return gapi
     */
    public static function authenticate($email, $password, $service, $logintoken = null, $logincaptcha = null)
    {
        $authMethod = new Google_Auth_ClientLogin();
        $authMethod->fetchToken($email, $password, $service, $logintoken, $logincaptcha);
        return $authMethod;
    }

    /**
     * Authenticate Google Account with ClientLogin
     *
     * @param String $email
     * @param String $password
     * @return gapiClientLogin
     */
    protected function fetchToken($email, $password, $service, $logintoken = null, $logincaptcha = null)
    {
        $postVariables = array(
            'accountType' => 'HOSTED_OR_GOOGLE',
            'Email' => $email,
            'Passwd' => $password,
            'source' => Google_Auth::AUTH_SOURCE_NAME,
            'service' => $service
        );
        if ($logintoken != null && $logincaptcha != null) {
            $postVariables['logintoken'] = $logintoken;
            $postVariables['logincaptcha'] = $logincaptcha;
        }
        $url = new DataType_Url(self::REQUEST_URL);
        $response = $url->post($postVariables);

        $code = $url->getResponseCode();

        $authToken = $this->parseBody($response);

        if (!is_array($authToken) || empty($authToken['Auth'])) {
            throw new Exception('Failed to authenticate user. Error: "' . htmlentities($response) . '"');
        }

        $this->authToken = $authToken['Auth'];
        return $this->authToken;
    }

    /**
     * Return the authorization method name
     *
     * @return String
     */
    protected function getMethodName()
    {
        return 'GoogleLogin';
    }

    /**
     * Returns the identifier of the token in the auth header
     *
     * @return String
     */
    protected function getTokenName()
    {
        return 'auth';
    }
}
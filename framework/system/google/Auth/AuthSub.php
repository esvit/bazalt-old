<?php

class Google_Auth_AuthSub extends Google_Auth
{
    const REQUEST_URL = 'https://www.google.com/accounts/AuthSubRequest';
    const SESSION_TOKEN_URL = 'https://www.google.com/accounts/AuthSubSessionToken';
    const REVOKE_TOKEN_URL = 'https://www.google.com/accounts/AuthSubRevokeToken';
    const TOKEN_INFO_URL = 'https://www.google.com/accounts/AuthSubTokenInfo';

    /**
     * Authenticate and return a seeded gapi instance
     *
     * @return Google_Auth_AuthSub
     */
    public static function authenticate($scope, $returnUrl = null, $checkOnly = false)
    {
        $authMethod = new Google_Auth_AuthSub();

        if (!isset($_GET['token'])) {
            // no token and we only want to check for one, so return
            if ($checkOnly) {
                return false;
            }
            $authMethod->performRequest($scope, $returnUrl);
        } else {
            $authMethod->fetchSessionToken();
        }
        return $authMethod;
    }

    /**
     * Construct the URL to which the user is redirected for logging into their Google account
     *
     * @param string $returnUrl
     */
    protected function getRequestUrl($scope, $returnUrl = null)
    {
        if ($returnUrl == null) {
            $returnUrl = DataType_Url::getRequestUrl(true, true);
        }

        $variables = array(
            'next' => $returnUrl,
            'scope' => $scope,
            'secure' => 0,
            'session' => 1
        );

        $url = new DataType_Url(self::REQUEST_URL);
        $url->setParams($variables);
        return DataType_Url::redirect($url);
    }

    /**
     * Redirect the user to the Google Accounts login page
     *
     * @param string $returnUrl
     */
    public function performRequest($scope, $returnUrl = null)
    {
        DataType_Url::redirect($this->getRequestUrl($scope, $returnUrl));
    }

    /**
    * Using the token returned as a GET variable, fetch the session token
    *
    * @return string
    */
    public function fetchSessionToken()
    {
        $url = new DataType_Url(self::SESSION_TOKEN_URL);
        $url->setHeaders($this->generateAuthHeader(urldecode($_GET['token'])));
        $response = $url->post();
        $authToken = $this->parseBody($response);

        if (!is_array($authToken) || empty($authToken['Token'])) {
            throw new Exception('Failed to authenticate user. Error: "' . strip_tags($response) . '"');
        }

        $this->authToken = $authToken['Token'];
        return $this->authToken;
    }

    /**
     * Return token information as an associative array
     *
     * @return array
     */
    public function getTokenInfo()
    {
        $url = new DataType_Url(self::TOKEN_INFO_URL);
        $url->setHeaders($this->generateAuthHeader($this->authToken));
        $response = $url->post();
        $info = $this->parseBody($response);

        if (!is_array($info)) {
            throw new Exception('Failed to retrieve token info. Error: "' . strip_tags($response) . '"');
        }

        return $info;
    }

    /**
     * Render the token invalid
     */
    public function revokeToken()
    {
        $url = new DataType_Url(self::REVOKE_TOKEN_URL);
        $url->setHeaders($this->generateAuthHeader($this->authToken));
        $response = $url->post();
        $result = $this->parseBody($response);
        $responseCode = $url->responseCode();

        if (substr($responseCode, 0, 1) != '2' || !is_array($result)) {
            throw new Exception('Failed to revoke token. Error: "' . strip_tags($response) . '"');
        }

        return $result;
    }

    /**
     * Check to see if a token has been sent back through $_GET variables
     */
    public static function checkToken()
    {
        return self::authenticate(null, true);
    }

    /**
     * Return the authorization method name
     *
     * @return string
     */
    protected function getMethodName()
    {
        return 'AuthSub';
    }

    /**
     * Returns the identifier of the token in the auth header
     *
     * @return string
     */
    protected function getTokenName()
    {
        return 'token';
    }
}
<?php
/**
 * GoogleAuthorization
 *
 * PHP versions 5
 *
 * LICENSE:
 * 
 * This library is free software; you can redistribute it
 * and/or modify it under the terms of the GNU Lesser General
 * Public License as published by the Free Software Foundation;
 * either version 2.1 of the License, or (at your option) any
 * later version.
 * 
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @category  Google
 * @package   BAZALT/Google/Auth
 * @author    Vitalii Savchuk <esvit666@gmail.com>
 * @license   http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version   $Revision: 67 $
 * @link      http://www.php-solves.com/
 */

using('Framework.System.Session');

/**
 * GoogleAuthorization
 *
 * @category Google
 * @package  BAZALT/Google/Auth
 * @author   Vitalii Savchuk <esvit666@gmail.com>
 * @license  http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version  $Rev: 67 $
 * @link     http://www.php-solves.com/
 */
class GoogleAuthorization extends Object
{
    const DEFAUL_AUTH_URL = '/google/openid/';

    /**
     * the google discover url
     */
    const GOOGLE_DISCOVER_URL = "https://www.google.com/accounts/o8/id";
    
    /**
     * some constant parameters
     */
    const OPENID_NS = "http://specs.openid.net/auth/2.0";

    /**
     * required for email attribute exchange
     */
    const OPENID_NS_EXT1 = "http://openid.net/srv/ax/1.0";
    const OPENID_EXT1_MODE = "fetch_request";
    const OPENID_EXT1_TYPE_EMAIL = "http://schema.openid.net/contact/email";
    const OPENID_EXT1_REQUIRED = "email";
    
    //parameters set by constructor
    private $mode;//the mode (checkid_setup, id_res, or cancel)
    private $response_nonce;
    private $return_to;//return URL
    private $realm;//the realm that the user is being asked to trust
    private $assoc_handle;//the association handle between this service and Google
    private $claimed_id;//the id claimed by the user
    private $identity;//for google, this is the same as claimed_id

    protected $op_endpoint;

    private $signed;
    private $sig;
    private $email;//the user's email address
    private $first;
    private $last;
    
    //if true, fetch email address
    private $require_email;

    const MODE_CHECK = 'checkid_setup';

    const MODE_ID = 'id_res';

    const MODE_CANCEL = 'cancel';
    
    const OPENID_CLAIMED_ID = 'http://specs.openid.net/auth/2.0/identifier_select';

    protected $urlPrefix;

    public function login($returnTo)
    {
        $this->init();
        $this->return_to = $this->getReturnTo($returnTo);
        $this->require_email = true;
        $this->redirect();
    }

    public function getRedirectUrl($returnTo)
    {
        $this->init();
        $this->return_to = $this->getReturnTo($returnTo);
        $this->require_email = true;
        return $this->getRequestURL()->__toString();
    }

    public function logout()
    {
        Session::Singleton()->GoogleOpenId = null;
    }

    public function isLogin()
    {
        return !empty(Session::Singleton()->GoogleOpenId);
    }

    public function getLogin()
    {
        return Session::Singleton()->GoogleOpenId;
    }

    public function __construct($urlPrefix = null)
    {
        $this->urlPrefix = $urlPrefix;
        parent::__construct();
    }

    /**
     * static creator that accepts only a return_to URL
     * this creator should be used when creating a GoogleOpenID for a redirect
     */
    protected function init($mode = self::MODE_CHECK, 
                            $op_endpoint = null, $response_nonce = null,
                            $return_to = self::DEFAUL_AUTH_URL, $realm = null, $assoc_handle,
                            $claimed_id = self::OPENID_CLAIMED_ID,
                            $signed = null, $sig = null,
                            $email = null, $require_email = true, $first = null, $last = null)
    {

        # if assoc_handle is null, fetch one
        if (is_null($assoc_handle)) {
            $assoc_handle = self::getAssociationHandle();
        }

        $return_to = $this->getReturnTo($return_to);

        # if realm is null, attempt to set it via return_to
        if (is_null($realm)) {
            if (!is_null($return_to)) {
                $pieces = parse_url($return_to);
                $realm = $pieces['scheme'] . '://' . $pieces['host'];
            }
        }

        $this->mode = $mode;
        $this->op_endpoint = $op_endpoint;
        $this->response_nonce = $response_nonce;
        $this->return_to = $return_to;
        $this->realm = $realm;
        $this->assoc_handle = $assoc_handle;
        $this->claimed_id = $claimed_id;
        $this->identity = $claimed_id;
        $this->signed = $signed;
        $this->sig = $sig;
        $this->email = $email;
        $this->first = $first;
        $this->last = $last;
        $this->require_email = ($require_email) ? true : false;
    }

    /**
     * Обрабатывает адрес возврата
     */
    public function getReturnTo($returnTo)
    {
        # if returnTo is a relative URL, make it absolute
        if (stripos($returnTo, 'http://') === false &&
            stripos($returnTo, 'https://') === false) {

            # if the first character is a slash, delete it
            if (substr($returnTo, 0, 1) == '/') {
                $returnTo = substr($returnTo, 1);
            }

            # get the position of server_name  
            $serverNamePos = stripos($returnTo, $_SERVER['SERVER_NAME']);

            # if server_name is already at position zero
            if ($serverNamePos != false && $serverNamePos == 0) {
                $returnTo = 'http://' . $returnTo;
            } else {
                $returnTo = 'http://' . $_SERVER['SERVER_NAME'] . '/' . $returnTo;
            }
        }
        return $returnTo;
    }

    /**
     * static creator that accepts an associative array of parameters and
     * sets only the setable attributes (does not overwrite constants)
     */
    public static function create($params)
    {
        foreach ($params as $param => $value) {
            switch ($param) {
                case 'openid_mode':
                    $mode = self::MODE_CANCEL;

                    # check validity of mode
                    if ($value == self::MODE_CHECK || 
                        $value == self::MODE_ID || 
                        $value == self::MODE_CANCEL) {

                        $mode = $value;
                    }
                    break;
                case 'openid_op_endpoint':
                    $op_endpoint = $value; break;
                case 'openid_response_nonce':
                    $response_nonce = $value; break;
                case 'openid_return_to':
                    $return_to = $value; break;
                case 'openid_realm':
                    $realm = $value; break;
                case 'openid_assoc_handle':
                    $assoc_handle = $value;break;
                case 'openid_claimed_id':
                    $claimed_id = $value; break;
                case 'openid_identity':
                    $claimed_id = $value; break;
                case 'openid_signed':
                    $signed = $value; break;
                case 'openid_sig':
                    $sig = $value; break;
                case 'openid_ext1_value_email':
                    $email = $value; break;
                case 'openid_ext1_value_first':
                    $first = $value; break;
                case 'openid_ext1_value_last':
                    $last = $value; break;
                case 'require_email':
                    $require_email = $value; break;
            }
        }

        # if require email is not set, set it to false
        if (!is_bool($require_email)) {
            $require_email = false;
        }

        # if mode is not set, set to default for redirection
        if (is_null($mode)) {
            $mode = self::MODE_CHECK;
        }

        # if return_to is not set and mode is checkid_setup, throw an error
        if (is_null($return_to) && $mode == self::MODE_CHECK) {
            throw new Exception('GoogleOpenID.create() needs parameter openid.return_to');
        }

        $class = __CLASS__;
        $cls = new $class();
        $cls->init($mode, $op_endpoint, $response_nonce, $return_to, $realm, $assoc_handle, $claimed_id, $signed, $sig, $email, $require_email, $first, $last);
        return $cls;
    }

    /**
     * creates and returns a GoogleOpenID from the $_GET variable
     */
    public static function getResponse()
    {
        return self::create($_GET);
    }
    
    /**
     * fetches an association handle from google. association handles are valid
     * for two weeks, so coders should do their best to save association handles
     * externally and pass them to createRequest()
     * NOTE: This function does not use encryption, but it SHOULD! At the time
     * I wrote this I wanted it done fast, and I couldn't seem to find a good
     * two-way SHA-1 or SHA-256 library for PHP. Encryption is not my thing, so
     * it remains unimplemented.
     */
    public static function getAssociationHandle($endpoint = null)
    {
        $requestUrl = is_null($endpoint) ? self::getEndPoint() : $endpoint;

        $url = new Url('https://www.google.com/accounts/o8/ud');

        $params = array(
            'openid.ns' => urlencode(self::OPENID_NS),
            'openid.mode' => 'associate',
            'openid.assoc_type' => 'HMAC-SHA1',
            'openid.session_type' => 'no-encryption'
        );
        $url->setParams($params);

        # get the contents of request URL
        $content = Url::getContent($url);

        # a handle to be returned
        $assoc_handle = null;

        # split the response into lines
        $lines = explode("\n", $content);

        $params = array();
        # loop through each line
        foreach($lines as $line) {
            $lien = trim($line);
            if (!empty($line)) {
                $arr = explode(':', $line, 2);
                $params[$arr[0]] = $arr[1];
            }
        }

        return $params['assoc_handle'];
    }
    
    /**
     * fetches an endpoint from Google
     */
    public static function getEndPoint()
    {
        $requestContent = Url::getContent(self::GOOGLE_DISCOVER_URL);

        # create a DOM document so we can extract the URI element
        $xml = simplexml_load_string($requestContent);

        return (string)$xml->XRD->Service->URI;
    }
    
    /**
     * returns an associative array of all openid parameters for this openid
     * session. the array contains all the GET attributes that would be sent
     * or that have been recieved, meaning:
     *
     * if mode = "cancel" returns only the mode and ns attributes
     * if mode = "id_res" returns all attributes that are not null
     * if mode = "checkid_setup" returns only attributes that need to be sent
     *           in the HTTP request
     */
    public function getArray()
    {
        # an associative array to return
        $ret = array();

        $ret['openid.ns'] = self::OPENID_NS;
      
        # if mode is cancel, return only ns and mode
        if ($this->mode == 'cancel') {
            $ret['openid.mode'] = "cancel";
            return $ret;
        }
      
        # set attributes that are returned for all cases
        if (!is_null($this->claimed_id)) {
            $ret['openid.claimed_id'] = $this->claimed_id;
            $ret['openid.identity'] = $this->claimed_id;
        }
        if (!is_null($this->return_to)) {
            $ret['openid.return_to'] = $this->return_to;
        }
        if (!is_null($this->realm)) {
            $ret['openid.realm'] = $this->realm;
        }
        if (!is_null($this->assoc_handle)) {
            $ret['openid.assoc_handle'] = $this->assoc_handle;
        }
        if (!is_null($this->mode)) {
            $ret['openid.mode'] = $this->mode;
        }

        # set attributes that are returned only if this is a request
        # and if getting email is required OR if this is a response and the
        # email is given
        if (($this->mode == self::MODE_CHECK && $this->require_email) ||
            ($this->mode == self::MODE_ID    && !is_null($this->email))) {

            $ret['openid.ns.ext1'] = self::OPENID_NS_EXT1;
            $ret['openid.ext1.mode'] = self::OPENID_EXT1_MODE;
            $ret['openid.ext1.type.email'] = self::OPENID_EXT1_TYPE_EMAIL;
            $ret['openid.ext1.required'] = self::OPENID_EXT1_REQUIRED;

            if (!is_null($this->email)) {
                $ret['openid.ext1.value.email'] = $this->email;
            }
        }

        # set attributes that are returned only if this is a response
        if ($this->mode == self::MODE_ID) {
            $ret['openid.op_endpoint'] = $this->op_endpoint;

            if(!is_null($this->response_nonce)) {
                $ret['openid.response_nonce'] = $this->response_nonce;
            }
            if(!is_null($this->signed)) {
                $ret['openid.signed'] = $this->signed;
            }
            if(!is_null($this->sig)) {
                $ret['openid.sig'] = $this->sig;
            }
        }

        # return the array
        return $ret;
    }

    /**
     * sends a request to google and fetches the url to which google is asking
     * us to redirect (unless the endpoint is already known, in which case the
     * function simply returns it)
     */
    public function endPoint()
    {
        //if we know the value of op_endpoint already
        if(!is_null($this->op_endpoint)) {
            return $this->op_endpoint;
        }

        //fetch the endpoint from Google
        $endpoint = self::getEndPoint();

        //save it
        $this->op_endpoint = $endpoint;

        //return the endpoint
        return $endpoint;
    }
    
    /**
     * returns the URL to which we should send a request (including all GET params)
     */
    public function getRequestURL()
    {
        $url = new Url($this->endPoint());
        $url->setParams($this->getArray());

        return $url;
    }
    
    /**
     * redirects the browser to the appropriate request URL
     */
    public function redirect()
    {
        $url = $this->getRequestURL();
        Session::Singleton()->googleAuthBackUrl = Url::getReferer();
        Url::redirect($url);
    }
    
    /**
     * returns true if the response was a success
     */
    public function isSuccess()
    {
        return ($this->mode == self::MODE_ID);
    }
    
    /**
     * returns the identity given in the response
     */
    public function getIdentity()
    {
        return ($this->mode != self::MODE_ID) ? null : $this->claimed_id;
    }
    
    /**
     * returns the email given in the response
     */
    public function getEmail()
    {
        return ($this->mode != self::MODE_ID) ? null : $this->email;
    }

    public function getFirst()
    {
        return ($this->mode != self::MODE_ID) ? null : $this->first;
    }

    public function getLast()
    {
        return ($this->mode != self::MODE_ID) ? null : $this->last;
    }

    /**
     * @return the assoc_handle
     */
    public function getAssocHandle()
    {
        return $this->assoc_handle;
    }
}
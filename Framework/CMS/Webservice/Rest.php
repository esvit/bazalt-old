<?php

namespace Framework\CMS\Webservice;

use Framework\CMS as CMS;

abstract class Rest extends \Tonic\Resource
{
    /**
     * Condition method to turn output into JSON
     */
    protected function json()
    {
        $this->before(function ($request) {
            if ($request->contentType == "application/json") {
                $request->data = json_decode($request->data);
            }
        });
        $this->after(function ($response) {
            $response->contentType = "application/json";

            if (isset($_GET['jsonp'])) {
                $response->body = $_GET['jsonp'].'('.json_encode($response->body).');';
            } else {
                $response->body = json_encode($response->body);
            }
        });
    }

    protected function action($action)
    {
        if (!isset($_GET['action']) || $_GET['action'] != $action) {
            throw new \Tonic\ConditionException;
        }
    }

    protected function param($type, $name, $r)
    {
        $name = trim($name, '$');
        if (!isset($this->params[$name])) {
            throw new \Tonic\ConditionException;
        }
        $value = $this->params[$name];

        switch ($type) {
            case 'int':
                if (!is_numeric($value)) {
                    throw new \Tonic\ConditionException;
                }
                break;
            default:
        }
    }

    /**
     * Condition method for the @secure annotation that checks the requests HTTP
     * authentication details against the username and password given in the annotation.
     *
     * @param str $username
     * @param str $password
     * @throws UnauthorizedException
     */
    function secure($username)
    {
        
        print_r(func_get_args());exit;
        if (
            isset($_SERVER['PHP_AUTH_USER']) && $_SERVER['PHP_AUTH_USER'] == $username &&
            isset($_SERVER['PHP_AUTH_PW']) && $_SERVER['PHP_AUTH_PW'] == $password
        ) {
            return;
        }
        #return new Response(401, 'No entry', array('wwwAuthenticate' => 'Basic realm="My Realm"'));
        throw new UnauthorizedException('No entry');
    }
}
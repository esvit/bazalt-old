<?php

namespace App\Rest\Webservice;

use Framework\CMS\Webservice\Response,
    Framework\System\Session\Session,
    Framework\System\Data as Data,
    Framework\CMS as CMS;

/**
 * @uri /app/auth
 * @uri /app/auth/:user_id
 */
class AuthService extends CMS\Webservice\Rest
{
    /**
     * @method POST
     * @provides application/json
     * @json
     * @return \Tonic\Response
     */
    public function login()
    {
        $user = null;
        $data = new Data\Validator((array)$this->request->data);
        $data->field('nickname')->required()->validator('exist_user', function($value) use (&$user, $data) {
            $user = CMS\Model\User::getUserByEmail($value);
            if (!$user) {
                $user = CMS\Model\User::getUserByLogin($value);
            }
            return ($user != null && $user->password == CMS\User::criptPassword($data->getData('password')));
        }, 'User with this email does not exists');
        $data->field('password')->required();
        if (!$data->validate()) {
            return new Response(400, $data->errors());
        }
        $user->login();
        return new Response(200, $user);
    }

    /**
     * @method DELETE
     * @provides application/json
     * @json
     * @return \Tonic\Response
     */
    public function logout()
    {
        CMS\User::logout();
        return new Response(200, true);
    }

    /**
     * @method GET
     * @provides application/json
     * @json
     * @return \Tonic\Response
     */
    public function get()
    {
        $user = CMS\User::get();
        if ($user->isGuest()) {
            return new Response(200, null);
        }
        return new Response(200, $user);
    }
}

<?php

namespace Components\Themes\Webservice;

use Tonic\Response,
    Framework\System\Session\Session,
    Framework\System\Data as Data,
    Framework\CMS as CMS;

/**
 * @uri /themes
 * @uri /themes/:theme_id
 */
class Themes extends CMS\Webservice\Rest
{
    /**
     * @method GET
     * @provides application/json
     * @json
     * @return \Tonic\Response
     */
    public function get()
    {
        /*$user = CMS\User::get();
        if ($user->isGuest()) {
            return new Response(200, null);
        }*/
        $result = [[
            'id' => 1,
            'title' => 'Default',
            'alias' => 'default'
        ]];
        return new Response(200, $result);
    }
}

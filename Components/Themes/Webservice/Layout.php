<?php

namespace Components\Themes\Webservice;

use Framework\System\Session\Session,
    Framework\System\Data as Data,
    Framework\CMS as CMS;

/**
 * @uri /themes/layout
 */
class Layout extends CMS\Webservice\Rest
{
    /**
     * @method GET
     * @provides application/json
     * @json
     * @return CMS\Webservice\Response
     */
    public function get()
    {
        /*$user = CMS\User::get();
        if ($user->isGuest()) {
            return new Response(200, null);
        }*/
        $templates = glob(__DIR__ . '/../views/layouts/*.html');
        $result = [];
        foreach ($templates as $template) {
            $result[basename($template)] = file_get_contents($template);
        }
        return new CMS\Webservice\Response(200, $result);
    }
}

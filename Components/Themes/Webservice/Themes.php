<?php

namespace Components\Themes\Webservice;

use Framework\CMS\Webservice\Response,
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
        $theme = CMS\Model\Theme::getById('default');
        $res = $theme->toArray();
        $res['title'] = 'Default';
        $result = [
            $res
        ];
        return new Response(200, $result);
    }

    /**
     * @method POST
     * @provides application/json
     * @json
     * @return \Tonic\Response
     */
    public function saveTheme($theme_id)
    {
        /*$user = CMS\User::get();
        if ($user->isGuest()) {
            return new Response(200, null);
        }*/
        $theme = CMS\Model\Theme::getById($theme_id);
        $data = (array)$this->request->data;
        $theme->settings = $data['settings'];
        $theme->save();

        \Components\Themes\Component::recompileLess(SITE_DIR . '/themes/' . $theme->id . '/assets/less/theme.less', $theme);
        
        return new Response(200, $theme);
    }
}

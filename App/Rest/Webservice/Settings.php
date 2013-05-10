<?php

namespace App\Rest\Webservice;

use Framework\CMS\Webservice\Response,
    Framework\System\Session\Session,
    Framework\System\Data as Data,
    Framework\CMS as CMS;

/**
 * @uri /app/settings
 */
class SettingsService extends CMS\Webservice\Rest
{
    /**
     * @method POST
     * @provides application/json
     * @json
     * @return \Tonic\Response
     */
    public function saveSettings()
    {
        $site = CMS\Bazalt::getSite();

        $data = new Data\Validator((array)$this->request->data);
        $data->field('title')->required();
        if (!$data->validate()) {
            return new Response(400, $data->errors());
        }
        $site->title = $data['title'];
        $site->is_allow_indexing = $data['is_allow_indexing'];
        $site->is_multilingual = $data['is_multilingual'];
        $site->save();
        return $this->get();
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
            return new Response(403, null);
        }
        $site = CMS\Bazalt::getSite();
        $settings = [
            'title' => $site->title,
            'secret_key' => $site->secret_key,
            'is_allow_indexing' => $site->is_allow_indexing == '1',
            'is_multilingual' => $site->is_multilingual == '1'
        ];
        return new Response(200, $settings);
    }

    /**
     * @method GET
     * @provides application/json
     * @priority 10
     * @action newSecretKey
     * @json
     * @return \Tonic\Response
     */
    public function newSecretKey()
    {
        $site = CMS\Bazalt::getSite();
        $key = \Framework\Core\Helper\Guid::newGuid();
        $site->secret_key = $key;
        $site->save();
        return new Response(200, ['key' => $key]);
    }
}

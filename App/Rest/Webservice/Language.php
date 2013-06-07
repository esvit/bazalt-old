<?php

namespace App\Rest\Webservice;

use Framework\CMS as CMS;
use Components\Gallery\Model\Photo;
use Components\Gallery\Model\Album;

/**
 * @uri /app/language
 */
class Language extends CMS\Webservice\Rest
{
    /**
     * @method GET
     * @provides application/json
     * @json
     * @return \Tonic\Response
     */
    public function get()
    {
        $result = [];
        if (isset($_GET['all'])) {
            $languages = CMS\Model\Language::getAll();
        } else {
            $languages = CMS\Language::getLanguages();
        }
        foreach ($languages as $language) {
            $result []= $language->toArray();
        }
        return new \Tonic\Response(200, $result);
    }
}
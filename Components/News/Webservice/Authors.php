<?php

namespace Components\News\Webservice;

use Framework\CMS\Webservice\Response,
    Framework\System\Session\Session,
    Framework\System\Data as Data,
    Framework\CMS as CMS;
use Components\News\Model\Article;

/**
 * @uri /news/authors
 */
class Authors extends CMS\Webservice\Rest
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
        $authors = Article::getAuthors();

        return new Response(200, $authors);
    }
}

<?php

namespace Components\Seo\Webservice;

use Framework\CMS as CMS;
use Framework\System\Data as Data,
    Components\Seo\Model as Model;

/**
 * @uri /seo/pages
 */
class Page extends CMS\Webservice\Rest
{
    /**
     * @method GET
     * @provides application/json
     * @json
     * @return CMS\Webservice\Response
     */
    public function get()
    {
        $route = null;
        if (isset($_GET['url'])) {
            $route = Model\Page::getByUrl($_GET['url']);
        }
        if (!$route) {
            $route = Model\Page::create();
            $route->url = $_GET['url'];
        }
        return new CMS\Webservice\Response(200, $route);
    }

    /**
     * @method POST
     * @provides application/json
     * @json
     * @return CMS\Webservice\Response
     */
    public function saveRoute()
    {
        $data = (array)$this->request->data;

        $user = CMS\User::get();
        $page = Model\Page::getByUrl($data['url']);
        if (!$page) {
            $page = Model\Page::create();
        }
        /*if ($user->isGuest()) {
            return new Response(200, null);
        }*/
        $page->site_id = CMS\Bazalt::getSiteId(); // important
        $page->url = $data['url'];
        $page->title = $data['title'];
        $page->keywords = $data['keywords'];
        $page->description = $data['description'];
        $page->save();

        return new CMS\Webservice\Response(200, $page);
    }
}
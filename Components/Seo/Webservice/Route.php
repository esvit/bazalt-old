<?php

namespace Components\Seo\Webservice;

use Framework\CMS as CMS;
use Framework\System\Data as Data,
    Components\Seo\Model as Model;

/**
 * @uri /seo/routes
 */
class Route extends CMS\Webservice\Rest
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
        if (isset($_GET['name'])) {
            $route = Model\Route::getByName($_GET['name']);
        }
        if (!$route) {
            $route = Model\Route::create();
            $route->name = $_GET['name'];
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
        $route = Model\Route::getByName($data['name']);
        if (!$route) {
            $route = Model\Route::create();
        }
        /*if ($user->isGuest()) {
            return new Response(200, null);
        }*/
        $route->site_id = CMS\Bazalt::getSiteId(); // important
        $route->name = $data['name'];
        $route->title = $data['title'];
        $route->keywords = $data['keywords'];
        $route->description = $data['description'];
        $route->save();

        return new CMS\Webservice\Response(200, $route);
    }
}
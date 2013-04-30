<?php

namespace Components\Menu\Webservice;

use Framework\CMS\Webservice\Response,
    Framework\System\Session\Session,
    Framework\System\Data as Data,
    Framework\CMS as CMS;

use Components\Menu\Model\Element;

/**
 * @uri /menu
 */
class Menu extends CMS\Webservice\Rest
{
    /**
     * @method GET
     * @provides application/json
     * @json
     * @return \Tonic\Response
     */
    public function getMenus()
    {
        $user = CMS\User::get();
        $menus = Element::getRoots();
        /*if ($user->isGuest()) {
            return new Response(200, null);
        }*/
        $result = [];
        foreach ($menus as $k => $menu) {
            $result[$k] = $menu->toArray();
            unset($result[$k]['children']);
        }
        return new Response(200, $result);
    }

    /**
     * @method GET
     * @priority 10
     * @action getTypes
     * @provides application/json
     * @json
     * @return \Tonic\Response
     */
    public function getTypes()
    {
        $result = [];
        $types = Element::getMenuTypes();

        foreach ($types as $k => $type) {
            $component = $type->component()->config();
            $result[$k] = [
                'component_id' => $component->id,
                'component_title' => $component->title,
                'name' => $type->getItemType()
            ];
        }
        return new Response(200, $result);
    }

    /**
     * @method GET
     * @priority 10
     * @action getSettings
     * @provides application/json
     * @json
     * @return \Tonic\Response
     */
    public function getSettings()
    {
        $result = [];
        $types = Element::getMenuTypes();

        foreach ($types as $type) {
            $component = $type->component()->config();
            if ($component->id == $_GET['component_id'] && $type->getItemType() == $_GET['menuType']) {
                $result = $type->getSettingsForm();
            }
        }
        return new Response(200, $result);
    }

    /**
     * @method PUT
     * @provides application/json
     * @json
     * @return \Tonic\Response
     */
    public function createMenu()
    {
        $data = new Data\Validator((array)$this->request->data);

        $menu = Element::create();
        $menu->title = $data->getData('title');
        $menu->save();
        $menu->root_id = $menu->id;
        $menu->save();

        return new Response(200, $menu);
    }

    /**
     * @method POST
     * @provides application/json
     * @json
     * @return \Tonic\Response
     */
    public function updateMenu()
    {
        $data = new Data\Validator((array)$this->request->data);

        $menu = null;
        $data->field('id')->required()->validator('exist_menu', function($value) use (&$menu) {
            $menu = Element::getById((int)$value);
            
            return ($menu != null);
        }, "Menu dosn't exists");

        if (!$data->validate()) {
            return new Response(400, $data->errors());
        }
        $titles = (array)$data->getData('title');
        $descriptions = (array)$data->getData('description');
        $menu->title = $titles['en'];
        $menu->description = $descriptions['en'];
        $menu->config = (array)$data->getData('config');
        $menu->is_publish = $data->getData('is_publish') ? '1' : '0';
        $menu->save();

        return new Response(200, $menu);
    }
}

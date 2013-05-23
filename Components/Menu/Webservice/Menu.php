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

        foreach ($types as $className => $menuItem) {
            $component = $menuItem->component()->config();
            $result []= [
                'component_id' => $component->id,
                'component_title' => $component->title,
                'class' => $className,
                'title' => $menuItem->getItemType()
            ];
        }
        return new Response(200, $result);
    }

    /**
     * @method POST
     * @priority 10
     * @action getSettings
     * @provides application/json
     * @json
     * @return \Tonic\Response
     */
    public function getSettings()
    {
        $data = new Data\Validator((array)$this->request->data);

        $menu = null;
        // 1. Check menu
        $data->field('id')->required()->validator('exist_menu', function($value) use (&$menu) {
            $menu = Element::getById((int)$value);
            
            return ($menu != null);
        }, "Menu dosn't exists");

        // 2. Check menu type
        $data->field('menuType')->required()->validator('exist_menuType', function($value) use (&$menu) {
            $types = Element::getMenuTypes();

            if (!isset($types[$value])) {
                return false;
            }
            $item = $types[$value];
            $menu->menuType = $value;
            $menu->component_id = $item->component()->config()->id;

            return true;
        }, "Menu type dosn't exists");

        if (!$data->validate()) {
            return new Response(400, $data->errors());
        }
        return new Response(200, $menu);
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
     * @method DELETE
     * @provides application/json
     * @json
     * @return \Tonic\Response
     */
    public function deleteMenuItem()
    {
        $data = new Data\Validator($_GET);

        $menu = null;
        $data->field('id')->required()->validator('exist_menu', function($value) use (&$menu) {
            $menu = Element::getById((int)$value);

            return ($menu != null);
        }, "Menu dosn't exists");

        if (!$data->validate()) {
            return new Response(400, $data->errors());
        }
        $menu->Elements->removeAll();
        $menu->delete();
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

        $languages = CMS\Language::getLanguages();
        
        $data->field('title')->validator('hasDefaultTranslate', function($value) use (&$menu, $languages, $data) {
            //$user = CMS\Model\User::getUserByEmail($value);
            foreach ($languages as $language) {
                \Framework\CMS\ORM\Localizable::setLanguage($language);
                $menu->title = $value->{$language->id};
                $menu->body = $data['body']->{$language->id};

                $menu->save();
            }

            return true;
        }, 'User with this email does not exists');

        if (!$data->validate()) {
            return new Response(400, $data->errors());
        }
        $menu->component_id = $data->getData('component_id');
        $menu->menuType = $data->getData('menuType');
        $menu->config = (array)$data->getData('config');
        $menu->is_publish = $data->getData('is_publish') ? '1' : '0';
        $menu->save();
        

        return new Response(200, $menu);
    }
}

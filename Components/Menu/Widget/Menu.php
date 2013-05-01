<?php

namespace Components\Menu\Widget;

use Framework\CMS as CMS,
    Components\Menu\Model\Element,
    Framework\Core\Helper\Url;

class Menu extends CMS\Widget
{
    public function fetch()
    {
        $this->options = (array)$this->options;
        $menu_id = $this->options['menu_id'];
        if (!empty($menu_id)) {
            /** @var Element $menu */
            $menu = Element::getById((int)$menu_id);
        }

        $this->view->assign('menu', null);
        $this->view->assign('menuitem', $menu);
        if (!$menu) {
            return parent::fetch();
        }

        $menu = $menu->getMenu();
        if (!$menu) {
            return parent::fetch();
        }
        $menu->setCurrentMenuByUrl(urldecode(Url::getRequestUrl(true)));

        // add CSS class
        if (isset($this->options['css'])) {
            $menu->css($this->options['css']);
        }

        // Attach to menu level
        if (isset($this->options['attach']) && $this->options['attach'] == 'on') {
            $level = (int)$this->options['attach_level'];
            for ($i = 0; $i <= $level; $i++) {
                if (!$menu->hasActiveMenu()) {
                    $menu = null;
                    break;
                }
                foreach ($menu->getItems() as $elem) {
                    if ($elem->isActive()) {
                        $menu = $elem;
                    }
                }
            }
        }
        if ($menu && isset($this->options['acl_check']) && $this->options['acl_check'] == 'on') {
            $this->_checkAcl($menu);
        }
        $this->view->assign('menu', $menu);

        return parent::fetch();
    }

    private function _checkAcl($menu)
    {
        $i = 0;
        foreach ($menu->getItems() as $elem) {
            if($elem instanceof ComMenu_Menu_Link) {
                $route = CMS_Mapper::getRoot()->find($elem->getUrl());
                if ($route && !$route->rule()->isAllowed()) {
                    $elem->visible(false);
                    $i++;
                }
            }
            if(count($elem->getItems()) > 0) {
                $childs = $this->_checkAcl($elem);
                if(count($elem->getItems()) == $childs) {
                    $elem->visible(false);
                }
            }
        }
        return $i;
    }

    public function getConfigPage()
    {
        $menus = Element::getRoots();

        $this->view->assign('menus', $menus);
        $this->view->assign('options', $this->options);
        return $this->view->fetch('widgets/menu-settings');
    }
}
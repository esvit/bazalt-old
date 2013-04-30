<?php

namespace Components\Menu\Menu;

use Framework\System\Routing\Route;

class Link extends \Framework\CMS\Menu\ComponentItem
{
    public function getItemType()
    {
        return __('Link', \Components\Menu\Component::getName());
    }

    public function prepare()
    {
        if ($this->element) {
            $this->addCss($this->element->config['css']);
        }
    }

    public function getSettingsForm()
    {
        $this->view->assign('menuitem', $this->element);
        return $this->view->fetch('menu/settings');
    }

    public function getUrl()
    {
        $url = $this->element->config['url'];
        return Route::urlPrefix() . $url;
    }
}
<?php

namespace Components\Pages\Menu;

use Framework\CMS as CMS,
    Components\Pages as Pages;

class Page extends \Framework\CMS\Menu\ComponentItem
{
    public function getItemType()
    {
        return __('Page', Pages\Component::getName());
    }

    public function getSettingsForm()
    {
        if ($this->element) {
            $this->view->assign('menuitem', $this->element);
            $config = $this->element->config;
            $this->view->assign('config', $config);
            $this->view->assign('page', $this->getPage());
        }
        return $this->view->fetch('admin/menu/page');
    }

    public function getUrl()
    {
        if (!($page = $this->getPage())) {
            return '#';
        }
        if(!$page->is_published) {
            $this->visible(false);
        }
        return $page->getUrl();
    }

    public function getPage()
    {
        $config = $this->element->config;

        if (isset($config['page_id'])) {
            $page = Pages\Model\Page::getById((int)$config['page_id']);
            return $page;
        }
        return null;
    }
}
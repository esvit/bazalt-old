<?php

namespace Components\Pages\Widget;

use Framework\CMS as CMS,
    Components\Pages\Model as Model;

class Page extends CMS\Widget
{
    public function fetch()
    {
        $pageId = $this->options['pageId'];

        $page = Model\Page::getById((int)$pageId);
        if (!$page) {
            return parent::fetch();
        }

        $this->view()->assign('page', $page);
        $this->view()->assign('show_title', $this->options['show_title'] == 'on');

        return parent::fetch();
    }

    public function getConfigPage()
    {
        $all_pages = Model\Page::getAll();

        $this->view->assign('all_pages', $all_pages);
        $this->view->assign('options', $this->options);
        return $this->view->fetch('widgets/page-settings');
    }
}
<?php

namespace Components\Pages\Controller;

use Framework\CMS as CMS,
    Components\Pages\Model\Page,
    Framework\System\Routing\Route;

class Index extends CMS\AbstractController
{
    public function viewAction($page)
    {
        if (!$page) {
            throw new CMS\Exception\PageNotFound();
        }
        $this->view->assign('page', $page);
        $this->view->assign('images', $page->Images->get());

        $this->view->display([
            'pages/page-' . $page->id,
            'pages/default'
        ]);
    }
}
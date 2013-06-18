<?php

namespace Components\Pages\Controller;

use Framework\CMS as CMS,
    Components\Pages\Model\Page,
    Bazalt\Routing\Route;

class Index extends CMS\AbstractController
{
    public function viewAction($page, $_meta)
    {
        if (!$page) {
            throw new CMS\Exception\PageNotFound();
        }
        $this->view->assign('page', $page);

        $_meta->assign('page_title', $page->title);

        $this->view->assign('images', $page->Images->get());

        $this->view->display([
            'pages/page-' . $page->id,
            'pages/default'
        ]);
    }
}
<?php

namespace App\Site\Controller;

use Framework\CMS as CMS;

class Index extends CMS\AbstractController
{
    public function defaultAction()
    {
        $this->view->display('index');
    }
}
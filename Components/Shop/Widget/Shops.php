<?php

namespace Components\Shop\Widget;

use Framework\CMS as CMS,
    Components\Shop\Component,
    Components\Shop\Model as Model;

class Shops extends CMS\Widget
{
    public function fetch()
    {
        $this->view()->assign('shops', Model\Shop::getCollection()->fetchAll());

        return parent::fetch();
    }
}
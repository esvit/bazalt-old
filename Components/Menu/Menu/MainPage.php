<?php

namespace Components\Menu\Menu;

use Bazalt\Routing\Route;

class MainPage extends \Framework\CMS\Menu\ComponentItem
{
    public function getItemType()
    {
        return __('Main page', \Components\Menu\Component::getName());
    }

    public function getUrl($params = null)
    {
        return Route::urlFor('home');
    }
}
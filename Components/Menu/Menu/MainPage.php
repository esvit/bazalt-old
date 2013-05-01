<?php

namespace Components\Menu\Menu;

use Framework\System\Routing\Route;

class MainPage extends \Framework\CMS\Menu\ComponentItem
{
    public function getItemType()
    {
        return __('Main page', \Components\Menu\Component::getName());
    }

    public function getUrl()
    {
        return Route::urlFor('home');
    }
}
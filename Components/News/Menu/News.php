<?php

class ComNewsChannel_Menu_News extends CMS_Menu_ComponentItem
{
    public function getItemType()
    {
        return __('News list', ComNewsChannel::getName());
    }

    public function getSettingsForm()
    {
        $this->view->assign('menuitem', $this->element);
        return $this->view->fetch('menu/news_settings');
    }

    public function getUrl()
    {
        return CMS_Mapper::urlFor('ComNewsChannel.AllNews');
    }
}
<?php

namespace Components\Gallery\Menu;

use Framework\CMS as CMS,
    Components\Gallery as Gallery;

class Album extends \Framework\CMS\Menu\ComponentItem
{
    public function getItemType()
    {
        return __('Gallery', Gallery\Component::getName());
    }

    public function getSettingsForm()
    {
        if ($this->element) {
            $this->view->assign('menuitem', $this->element);
            $config = $this->element->config;
            $this->view->assign('config', $config);
            $this->view->assign('album', $this->getAlbum());
        }
        return $this->view->fetch('admin/menu/album');
    }

    public function getUrl()
    {
        if (!($album = $this->getAlbum())) {
            return CMS\Route::urlFor('Gallery.List');
        }
        if(!$album->is_published) {
            $this->visible(false);
        }
        return $album->url();
    }

    public function getAlbum()
    {
        $config = $this->element->config;

        if (isset($config['album_id'])) {
            $page = Gallery\Model\Album::getById((int)$config['album_id']);
            return $page;
        }
        return null;
    }
}
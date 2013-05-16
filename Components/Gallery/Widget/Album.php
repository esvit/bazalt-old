<?php

namespace Components\Gallery\Widget;

use Framework\CMS as CMS,
    Components\Gallery\Component,
    Components\Gallery\Model as Model;

class Album extends CMS\Widget
{
    public function fetch()
    {
        $gallery = $this->getGallery();
        if ($gallery) {
            $photos = Model\Photo::getByCategory($gallery);
            $this->view()->assign('photos', $photos->fetchAll());
            $this->view()->assign('category', $gallery);
        }

        return parent::fetch();
    }

    public function getConfigPage($config)
    {
        $this->view()->assign('config', $this->options);
        $this->view()->assign('gallery', $this->getGallery());

        $root = Model\Album::getRoot();
        $this->view()->assign('tree', $root);
        return $this->view()->fetch('widgets/album-settings');
    }

    public function getGallery()
    {
        if (isset($this->options['gallery_id'])) {
            $gallery = Model\Album::getById((int)$this->options['gallery_id']);
            return $gallery;
        }
        return null;
    }
}
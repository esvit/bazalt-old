<?php

namespace Components\Gallery\Controller;

use Framework\CMS as CMS;
use Components\Gallery\Model\Album;
use Components\Gallery\Model\Photo;

class Index extends CMS\AbstractController
{
    public function defaultAction()
    {
        $albums = Album::getCollection(true);

        $this->view->assign('albums', $albums->getPage());
        $this->view->assign('pager', $albums->getPager('Gallery.List'));

        $this->view->display('gallery/albums');
    }

    public function albumAction($album)
    {
        $album = Album::getByAlias($album);
        if (!$album || !$album->is_publish) {
            throw new CMS\Exception\PageNotFound();
        }
        $images = Photo::getCollection($album, true);

        $this->view->assign('album', $album);
        $this->view->assign('images', $images->getPage());
        $this->view->assign('pager', $images->getPager('Gallery.Album'));

        $this->view->display('gallery/album');
    }

    public function photoAction($album, $photo)
    {
        $album = Album::getByAlias($album);
        $photo = Photo::getById($photo);
        if (!$album || !$photo || !$album->is_publish || $photo->album_id != $album->id) {
            throw new CMS\Exception\PageNotFound();
        }
        $images = Photo::getCollection($album, true);

        $this->view->assign('album', $album);
        $this->view->assign('image', $photo);

        $this->view->display('gallery/photo');
    }
}
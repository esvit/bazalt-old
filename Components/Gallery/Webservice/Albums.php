<?php

namespace Components\Gallery\Webservice;

use Framework\CMS\Webservice\Response,
    Framework\System\Session\Session,
    Framework\System\Data as Data,
    Framework\CMS as CMS;
use Components\Gallery\Model\Photo;
use Components\Gallery\Model\Album;
use Components\Gallery\Component;

/**
 * @uri /gallery
 * @uri /gallery/:album_id
 * @uri /gallery/:album_id/:photo_id
 */
class Albums extends CMS\Webservice\Rest
{
    /**
     * @method GET
     * @provides application/json
     * @json
     * @return \Framework\CMS\Webservice\Response
     */
    public function getAlbums()
    {
        $user = CMS\User::get();
        $albums = Album::getCollection($user->isGuest());
        /*if ($user->isGuest()) {
            return new Response(200, null);
        }*/
        $albums = $albums->fetchAll();//->getPage(1, 10);

        return new Response(200, $albums);
    }

    /**
     * @method GET
     * @priority 10
     * @param  int $album_id
     * @provides application/json
     * @json
     * @return \Framework\CMS\Webservice\Response
     */
    public function getPhotos($album_id)
    {
        $user = CMS\User::get();
        $album = Album::getById($album_id);
        if (!$album->is_published && !($user->hasRight(Component::getName(), Component::ACL_HAS_ACCESS))) {
            return new Response(403, null);
        }
        $collection = Photo::getCollection($album);
        try {
            $photos = $collection->getPage((int)$_GET['page'], 50);
        } catch (\ORM_Exception_Collection $e) { // Invalid page number
            $photos = [];
        }
        return new Response(200, $photos);
    }

    /**
     * @method PUT
     * @provides application/json
     * @json
     * @return \Framework\CMS\Webservice\Response
     */
    public function createAlbum()
    {
        $data = new Data\Validator((array)$this->request->data);

        $album = Album::create();
        $album->title = $data->getData('title');
        $album->alias = substr(\Framework\Core\Helper\Url::cleanUrl($album->title), 0, 50);
        $album->save();

        return new Response(200, $album->toArray());
    }

    /**
     * @method POST
     * @provides application/json
     * @json
     * @return \Framework\CMS\Webservice\Response
     */
    public function saveAlbum()
    {
        $data = new Data\Validator((array)$this->request->data);

        $album = null;
        $data->field('id')->required()->validator('exist_album', function($value) use (&$album) {
            $album = Album::getById($value);
            
            return ($album != null);
        }, "Album dosn't exists");

        if (!$data->validate()) {
            return new Response(400, $data->errors());
        }
        $album->is_hidden = $data->getData('is_hidden') ? '1' : '0';
        $album->is_published = $data->getData('is_published') ? '1' : '0';
        $album->save();

        return new Response(200, $album);
    }

    /**
     * @method DELETE
     * @provides application/json
     * @json
     * @return CMS\Webservice\Response
     */
    public function deleteAlbum($album_id)
    {
        $user = CMS\User::get();
        $album = Album::getById($album_id);
        /*if ($user->isGuest()) {
            return new Response(200, null);
        }*/
        $album->delete();
        return new Response(200, true);
    }

    /**
     * @method POST
     * @priority 10
     * @param  int $album_id
     * @param  int $photo_id
     * @provides application/json
     * @json
     * @return \Framework\CMS\Webservice\Response
     */
    public function savePhoto($album_id, $photo_id)
    {
        $album = Album::getById($album_id);
        $photo = Photo::getById($photo_id);
        $data = (array)$this->request->data;

        /*if (!$data->validate()) {
            return new Response(400, $data->errors());
        }*/
        $photo->title = $data['title']->ukr;
        $photo->description = $data['description']->ukr;
        $photo->save();

        return new Response(200, $photo);
    }

    /**
     * @method POST
     * @priority 10
     * @param  int $album_id
     * @provides application/json
     * @json
     * @return \Framework\CMS\Webservice\Response
     */
    public function uploadPhoto($album_id)
    {
        $user = CMS\User::get();
        $album = Album::getById($album_id);
        /*if ($user->isGuest()) {
            return new Response(200, null);
        }*/

        $uploads_dir = '/uploads';

        if ($_FILES["file1"]["error"] == UPLOAD_ERR_OK) {
            $tmp_name = $_FILES["file1"]["tmp_name"];
            $name = $_FILES["file1"]["name"];
            $uploadName = CMS\Bazalt::uploadFilename($name, 'gallery');
            if (move_uploaded_file($tmp_name, $uploadName)) {
                $photo = Photo::create();
                $photo->title = pathinfo($name, PATHINFO_FILENAME);
                $photo->image = relativePath($uploadName);

                list($width, $height, $type, $attr) = getimagesize($uploadName);
                $photo->width = $width;
                $photo->height = $height;
                $photo->order = $album->getMaxOrder() + 1;
                $album->Photos->add($photo);
                
                $album->images_count++;
                $album->save();
            }
        } else {
            switch ($_FILES["file1"]["error"]) {
                case UPLOAD_ERR_INI_SIZE:
                    $message = "The uploaded file exceeds the upload_max_filesize directive in php.ini";
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    $message = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $message = "The uploaded file was only partially uploaded";
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $message = "No file was uploaded";
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $message = "Missing a temporary folder";
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    $message = "Failed to write file to disk";
                    break;
                case UPLOAD_ERR_EXTENSION:
                    $message = "File upload stopped by extension";
                    break;

                default:
                    $message = "Unknown upload error";
                    break;
            }
            return new Response(500, $message);
        }
        return new Response(200, $photo);
    }

    /**
     * @method PUT
     * @priority 10
     * @param  int $album_id
     * @provides application/json
     * @json
     * @return \Framework\CMS\Webservice\Response
     */
    public function updateOrders($album_id)
    {
        $data = (array)$this->request->data;
        $album = Album::getById($album_id);

        $count = $album->Photos->count();
        $orders = [];
        foreach ($data['orders'] as $order => $id) {
            $pos = $count - $order;
            $orders[$id] = $pos;
            Photo::updatePhotoOrder($id, $pos);
        }
        return new Response(200, $orders);
    }

    /**
     * @method DELETE
     * @priority 10
     * @param  int $album_id
     * @param  int $photo_id
     * @provides application/json
     * @json
     * @return \Framework\CMS\Webservice\Response
     */
    public function deletePhoto($album_id, $photo_id)
    {
        $user = CMS\User::get();
        $album = Album::getById($album_id);
        $photo = Photo::getById($photo_id);
        /*if ($user->isGuest()) {
            return new Response(200, null);
        }*/
        $album->images_count--;
        $album->save();
        $photo->delete();
        return new Response(200, true);
    }
}

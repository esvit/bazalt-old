<?php

namespace Components\Gallery\Webservice;

use Tonic\Response,
    Framework\System\Session\Session,
    Framework\System\Data as Data,
    Framework\CMS as CMS;
use Components\Gallery\Model\Photo;
use Components\Gallery\Model\Album;

/**
 * @uri /gallery/:album_id/photo/:id
 */
class PhotoRest extends CMS\Webservice\Rest
{
    /**
     * @method GET
     * @priority 10
     * @param  int $album_id
     * @param  int $id
     * @provides application/json
     * @json
     * @return \Tonic\Response
     */
    public function getPhoto($album_id, $id)
    {
        $album = Album::getById($album_id);
        /*if ($user->isGuest()) {
            return new Response(200, null);
        }*/
        $photo = Photo::getById($id);
        return new Response(200, $photo->toArray());
    }
}

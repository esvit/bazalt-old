<?php

namespace Components\Themes\Webservice;

use Tonic\Response,
    Framework\System\Session\Session,
    Framework\System\Data as Data,
    Framework\CMS as CMS;

/**
 * @uri /themes/:id/file
 * @uri /themes/:id/file/:file_id
 */
class File extends CMS\Webservice\Rest
{
    protected static $files = [
        [
            'type' => 'category',
            'title' => 'Views'
        ],
        [
            'id' => 5,
            'type' => 'file',
            'name' => 'views/layout.twg',
            'title' => 'Базова сторінка',
            'canUseLayout' => true,
            'contentType' => 'twig'
        ],
        [
            'type' => 'category',
            'title' => 'Галерея'
        ],
        [
            'id' => 1,
            'type' => 'file',
            'name' => 'views/gallery/albums.twg',
            'title' => 'Список альбомів',
            'contentType' => 'html'
        ],
        [
            'id' => 2,
            'type' => 'file',
            'name' => 'views/gallery/album.twg',
            'title' => 'Альбом',
            'contentType' => 'html'
        ],
        [
            'type' => 'category',
            'title' => 'Styles'
        ],
        [
            'id' => 3,
            'type' => 'file',
            'name' => 'assets/css/app.css',
            'title' => 'app.css',
            'contentType' => 'css'
        ],
        [
            'id' => 4,
            'type' => 'file',
            'name' => 'assets/less/main.less',
            'title' => 'main.less',
            'contentType' => 'less'
        ]
    ];

    /**
     * @method GET
     * @priority 10
     * @param  int $id
     * @param  int $file_id
     * @provides application/json
     * @json
     * @return \Tonic\Response
     */
    public function load($id, $file_id)
    {
        $fileOpen = null;
        foreach (self::$files as $file) {
            if ($file['id'] == $file_id) {
                $fileOpen = $file;
                break;
            }
        }
        //$fileOpen['contentType'] = 'html';
        $fileOpen['content'] = file_get_contents(SITE_DIR . '/themes/default/' . $fileOpen['name']);
        $fileOpen['theme_id'] = $id;
        return new Response(200, $fileOpen);
    }

    /**
     * @method POST
     * @param  int $id
     * @param  int $file_id
     * @provides application/json
     * @json
     * @return \Tonic\Response
     */
    public function save($id, $file_id)
    {
        $data = (array)$this->request->data;
        $fileOpen = null;
        foreach (self::$files as $file) {
            if ($file['id'] == $file_id) {
                $fileOpen = $file;
                break;
            }
        }
        /*$user = CMS\User::get();
        if ($user->isGuest()) {
            return new Response(200, null);
        }*/
        $content = $data['content'];
        file_put_contents(SITE_DIR . '/themes/default/' . $fileOpen['name'], $content);

        return new Response(200, $data);
    }

    /**
     * @method GET
     * @provides application/json
     * @json
     * @return \Tonic\Response
     */
    public function get($id)
    {
        /*$user = CMS\User::get();
        if ($user->isGuest()) {
            return new Response(200, null);
        }*/
        $result = self::$files;
        return new Response(200, $result);
    }
}

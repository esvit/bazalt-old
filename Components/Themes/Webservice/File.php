<?php

namespace Components\Themes\Webservice;

use Framework\CMS\Webservice\Response,
    Framework\System\Session\Session,
    Framework\System\Data as Data,
    Framework\CMS as CMS;

/**
 * @uri /themes/:theme_id/file
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
     * @param  string $theme_id
     * @action loadFile
     * @provides application/json
     * @json
     * @return \Tonic\Response
     */
    public function load($theme_id)
    {
        $file_id = $_GET['file'];
        $fileOpen = null;

        $fileOpen['contentType'] = 'less';
        $fileOpen['content'] = file_get_contents(SITE_DIR . $file_id);
        return new Response(200, $fileOpen);
    }

    /**
     * @method POST
     * @param  string $theme_id
     * @provides application/json
     * @json
     * @return \Tonic\Response
     */
    public function save($theme_id)
    {
        $data = (array)$this->request->data;
        $file_id = $data['file'];

        /*$user = CMS\User::get();
        if ($user->isGuest()) {
            return new Response(200, null);
        }*/
        $content = $data['content'];
        file_put_contents(SITE_DIR . $file_id, $content);
        if (pathinfo($file_id, PATHINFO_EXTENSION) == 'less') {
            \Components\Themes\Component::recompileLess(SITE_DIR . $file_id, CMS\Model\Theme::getById('default'));
        }

        return new Response(200, $data);
    }

    /**
     * @method GET
     * @provides application/json
     * @json
     * @return \Tonic\Response
     */
    public function get($theme_id)
    {
        /*$user = CMS\User::get();
        if ($user->isGuest()) {
            return new Response(200, null);
        }*/
        $dir = SITE_DIR . '/themes/' . CMS\Bazalt::getSite()->theme_id . '/';
        $files = [];
        
        $files [] = [
            'type' => 'category',
            'title' => 'LESS'
        ];
        foreach (glob($dir . '/assets/less/*.less') as $file) {
            $files [] = [
                'type' => 'file',
                'file' => relativePath($file),
                'name' => basename($file),
                'contentType' => 'less',
                'theme_id' => CMS\Bazalt::getSite()->theme_id
            ];
        }
        $files [] = [
            'type' => 'category',
            'title' => 'Templates'
        ];
        foreach (glob($dir . '/views/*.twg') as $file) {
            $files [] = [
                'type' => 'file',
                'file' => relativePath($file),
                'name' => basename($file),
                'contentType' => 'twg',
                'theme_id' => CMS\Bazalt::getSite()->theme_id
            ];
        }
        foreach (glob($dir . '/views/*/*.twg') as $file) {
            $files [] = [
                'type' => 'file',
                'file' => relativePath($file),
                'name' => ltrim(relativePath($file, $dir . '/views'), '/'),
                'contentType' => 'twig',
                'theme_id' => CMS\Bazalt::getSite()->theme_id
            ];
        }
        
        $result = $files;
        return new Response(200, $result);
    }
}

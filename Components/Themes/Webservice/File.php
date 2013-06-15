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
    private static function _listFiles($path, $pattern, $type = null, $basePath = null)
    {
        if (!$type) {
            $type = $pattern;
        }
        if (!$basePath) {
            $basePath = $path;
        }
        $files = [];
        if (is_dir($path)) {
            if ($handle = opendir($path)) {
                while (($name = readdir($handle)) !== false) {
                    if ($name[0] != '.' && is_dir($path . "/" . $name)) {
                        $files = array_merge($files, self::_listFiles($path . "/" . $name, $pattern, $type, $basePath));
                    } else {
                        if (preg_match("#(.*)\." . $pattern . "$#", $name)) {
                            $files [] = [
                                'type' => 'file',
                                'file' => relativePath($path . "/" . $name),
                                'name' => ltrim(relativePath($path . "/" . $name, $basePath), '/'),
                                'contentType' => $type,
                                'theme_id' => CMS\Bazalt::getSite()->theme_id
                            ];
                        }
                    }
                }
               
                closedir($handle);
            }
        }
        return $files;
    }

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

        $theme = CMS\Model\Theme::getById($theme_id);
        /*$user = CMS\User::get();
        if ($user->isGuest()) {
            return new Response(200, null);
        }*/
        $content = $data['content'];
        file_put_contents(SITE_DIR . $file_id, $content);
        if (pathinfo($file_id, PATHINFO_EXTENSION) == 'less') {
            \Components\Themes\Component::recompileLess(SITE_DIR . $file_id, $theme);
        }
        \Components\Themes\Component::recompileLess(SITE_DIR . '/themes/' . $theme->id . '/assets/less/theme.less', $theme);

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
        $files = array_merge($files, self::_listFiles($dir . '/assets/less', 'less'));

        $files [] = [
            'type' => 'category',
            'title' => 'CSS'
        ];
        $files = array_merge($files, self::_listFiles($dir . '/assets/css', 'css'));

        $files [] = [
            'type' => 'category',
            'title' => 'Templates'
        ];
        $files = array_merge($files, self::_listFiles($dir . '/views', 'twg', 'twig'));

        $result = $files;
        return new Response(200, $result);
    }
}

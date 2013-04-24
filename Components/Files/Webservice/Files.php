<?php

//require_once dirname(__FILE__) . '/../elFinderVolumeORM.class.php';
namespace Components\Files\Webservice;

use Framework\CMS as CMS;

class Files extends CMS\Webservice\Rest
{
    public static function access($attr, $path, $data, $volume)
    {
        return strpos(basename($path), '.') === 0   // if file/folder begins with '.' (dot)
            ? !($attr == 'read' || $attr == 'write')  // set read+write to false, other (locked+hidden) set to true
            : ($attr == 'read' || $attr == 'write');  // else set read+write to true, locked+hidden to false
    }

    public function __execute()
    {
        $roots = ComFileStorage_Model_File::getBySite(CMS_Bazalt::getSiteId());

        if (count($roots) == 0) {
            ComFileStorage_Model_File::createRoot('Home', CMS_Bazalt::getSiteId());

            $roots = ComFileStorage_Model_File::getBySite(CMS_Bazalt::getSiteId());
        }
        $opts = array(
            // 'debug' => true,
            'roots' => array()
        );
        foreach ($roots as $root) {
            $opts['roots'] []= array(
                'driver'        => 'ORM',
                'URL'           => CMS_Mapper::urlFor('ComFileStorage.ViewRoot'),
                'path'          => $root->id,
                'tmpPath'       => TEMP_DIR . '/',         // path to files (REQUIRED)
                'tmbPath'       => UPLOAD_DIR . '/.tmb', // URL to files (REQUIRED),
                'tmbURL'        => relativePath(UPLOAD_DIR) . '/.tmb', // URL to files (REQUIRED)
                'accessControl' => array(__CLASS__, 'access')             // disable and hide dot starting files (OPTIONAL)
            );
        }

        $connector = new elFinderConnector(new elFinder($opts));
        $connector->run();
        exit;
    }
}
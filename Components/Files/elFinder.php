<?php

namespace Components\Files;

require 'elFinderVolumeORM.class.php';

use Framework\CMS as CMS,
    Components\Files\Model\File,
    Bazalt\Routing\Route;

class elFinder extends \elFinder
{
    public static function connector()
    {
        $connector = new \elFinderConnector(new elFinder());
        return $connector;
    }

    public function __construct($opts = [])
    {
        $roots = File::getBySite(CMS\Bazalt::getSiteId());

        if (count($roots) == 0) {
            File::createRoot('Uploads', CMS\Bazalt::getSiteId());

            $roots = File::getBySite(CMS\Bazalt::getSiteId());
        }
        $opts = array(
            // 'debug' => true,
            'roots' => array()
        );
        define('UPLOAD_DIR', SITE_DIR . '/uploads');

        foreach ($roots as $root) {
            $opts['roots'] []= [
                'driver'        => 'ORM',
                'URL'           => Route::urlFor('Files.Downloads'),
                'path'          => $root->id,
                'tmpPath'       => TEMP_DIR . '/',         // path to files (REQUIRED)
                'tmbPath'       => UPLOAD_DIR . '/.tmb', // URL to files (REQUIRED),
                'tmbURL'        => relativePath(UPLOAD_DIR) . '/.tmb', // URL to files (REQUIRED)
                'accessControl' => array(__CLASS__, 'access')             // disable and hide dot starting files (OPTIONAL)
            ];
        }
        $opts['roots'] []= [
            'quarantine'    => false,
            'driver'        => 'LocalFileSystem',
            'alias'         => __('Theme', Component::getName()),
            //'URL'           => Route::urlFor('Files.Downloads'),
            'path'          => SITE_DIR . '/themes/default',
            'tmpPath'       => TEMP_DIR . '/',         // path to files (REQUIRED)
            'tmbPath'       => UPLOAD_DIR . '/.tmb', // URL to files (REQUIRED),
            'tmbURL'        => relativePath(UPLOAD_DIR) . '/.tmb', // URL to files (REQUIRED)
            //'accessControl' => array(__CLASS__, 'access')             // disable and hide dot starting files (OPTIONAL)
        ];

        parent::__construct($opts);
        /* Adding new command */
        unset($this->commands['duplicate']);
        $this->commands['desc'] = array('target' => true, 'content' => false);
        $this->commands['owner'] = array('target' => true);
        $this->commands['downloadcount'] = array('target' => true);
    }

    public static function access($attr, $path, $data, $volume)
    {
        return strpos(basename($path), '.') === 0   // if file/folder begins with '.' (dot)
            ? !($attr == 'read' || $attr == 'write')  // set read+write to false, other (locked+hidden) set to true
            : ($attr == 'read' || $attr == 'write');  // else set read+write to true, locked+hidden to false
    }

    protected function getVolume($commandName, $args, &$error)
    {
        $target = $args['target'];
        $error = array(self::ERROR_UNKNOWN, '#' . $target);

        if (!($volume = $this->volume($target)) || !($file = $volume->file($target))) {
            $error = array('error' => $this->error($error, self::ERROR_FILE_NOT_FOUND));
            return null;
        }

        $error[1] = $file['name'];

        if ($volume->commandDisabled($commandName)) {
            $error = array('error' => $this->error($error, self::ERROR_ACCESS_DENIED));
            return null;
        }
        return $volume;
    }

    protected function desc($args)
    {
        $target = $args['target'];
        $desc = $args['content'];
        $volume = $this->getVolume('desc', $args, $error);
        if (!$volume) {
            return $error;
        }
        if (($desc = $volume->desc($target, $desc)) == -1) {
            return array('error' => $this->error($error, $volume->error()));
        }
        return array('desc' => $desc);
    }

    protected function downloadcount($args)
    {
        $target = $args['target'];
        $volume = $this->getVolume('downloadcount', $args, $error);
        if (!$volume) {
            return $error;
        }
        if (($downloadcount = $volume->downloadcount($target)) == -1) {
            return array('error' => $this->error($error, $volume->error()));
        }
        return array('downloadcount' => $downloadcount);
    }

    protected function owner($args)
    {
        $target = $args['target'];
        $volume = $this->getVolume('owner', $args, $error);
        if (!$volume) {
            return $error;
        }
        if (($owner = $volume->owner($target)) == -1) {
            return array('error' => $this->error($error, $volume->error()));
        }
        if ($owner != null) {
            $user = CMS\Model\User::getById($owner);
            if ($user) {
                $owner = '<a href="#!/users/user' . $user->id . '">' . $user->login . '</a>';
            } else {
                $owner = null;
            }
        }
        if ($owner == null) {
            $owner = __('Guest', Component::getName());
        }
        return array('owner' => $owner);
    }
}
<?php

namespace App\Thumb;

using('Framework.Vendors.Imagine');

use Framework\CMS as CMS;
use Bazalt\Routing\Route;
use Framework\System\Session\Session;

class Application extends CMS\Application
{
    public function init()
    {
        parent::init();

        $mode = \Imagine\Image\ImageInterface::THUMBNAIL_OUTBOUND;

        $file = SITE_DIR . $this->url;
        if (!is_file($file)) {
            $file = __DIR__ . '/assets/images/not_found.gif';
        }

        $imagine = new \Imagine\Gd\Imagine();

        $width = isset($_GET['w']) ? (int)$_GET['w'] : null;
        $height = isset($_GET['h']) ? (int)$_GET['h'] : null;
        
        $key = md5($file .  '-' . $width . 'x' . $height);
        $staticFile = SITE_DIR . PATH_SEP . 'static/' . $key{0} . '/' . $key{1} . $key{2} . '/' . $key{3} . $key{4} . $key{5};
        if (!is_dir($staticFile)) {
            mkdir($staticFile, 0777, true);
        }
        $staticFile .= '/' . substr($key, 6);
        if ($width > 300 || $height > 300) {
            $staticFile .= '.png';

            header('Content-Type: image/png'); 
        } else {
            $staticFile .= '.jpg';

            header('Content-Type: image/jpeg'); 
        }

        if (!is_file($staticFile)) {
            $image = $imagine->open($file);
            $size = $image->getSize();
            if (!$width) {
                $width = $size->getWidth() * (float)$height / $size->getHeight();
            }
            if (!$height) {
                $height = $size->getHeight() * (float)$width / $size->getWidth();
            }

            $white = new \Imagine\Image\Color('fff');

            $image = $image->thumbnail(new \Imagine\Image\Box($width, $height), $mode);

            $image->save($staticFile);
        }
        readfile($staticFile);
        exit;
    }
}
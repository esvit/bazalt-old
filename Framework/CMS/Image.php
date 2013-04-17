<?php

//using('Framework.System.Drawing');
//using('Framework.System.Metadata');
namespace Framework\CMS;

using('Framework.Vendors.PHPThumb');

class Image
{
    public static $extensions = array('jpg', 'jpeg', 'png', 'gif', 'bmp');

    protected $filename = null;

    public $image = null;

    protected $actions = null;

    public function __construct($precompiled = null)
    {
        if ($precompiled != null) {
            $this->actions = $precompiled->actions;
            $this->filename = SITE_DIR . $precompiled->file;

            if (!file_exists($this->filename)) {
                throw new CMS_Exception_PageNotFound($this->filename);
            }
            $this->image = new \PHPThumb\GD($this->filename);
        }
    }

    public static function createPrecompiled($file, $actions = array())
    {
        $pre = new \stdClass;
        $pre->file = $file;
        $pre->actions = $actions;
        return $pre;
    }

    public function doActions()
    {
        foreach ($this->actions as $action) {
            $class = __CLASS__;
            if (isset($action->class)) {
                $class = $action->class;
            }
            if (!$this->image->isTrueColor()) {
                $this->image = $this->image->asTrueColor();
            }
            $params = array($this->image);
            if (isset($action->params)) {
                $params = array_merge($params, $action->params);
            }
            $this->image = call_user_func_array(array($class, $action->method), $params);
            if (!$this->image) {
                throw new Exception('Invalid action "' . $class . '::' . $action->method . '"');
            }
        }
        return $this;
    }

    public static function resize($image, $width, $height, $type = 'inside')
    {
        $width = ($width) ? $width : null;
        $height = ($height) ? $height : null;
        return $image->resize($width, $height, $type);
    }

    public static function transparentBackground($image, $width, $height)
    {
        $transparentColor = $image->allocateColorAlpha(255, 255, 255, 127);
        $image = $image->resizeCanvas($width, $height, 'center', 'center', $transparentColor);
        $image->setTransparentColor($transparentColor);

        return $image;
    }

    public static function autoCrop($image, $width, $height)
    {
        //if ($image->getHeight() <= $height) {
            $image = $image->resize($width, $height, 'outside')
                           ->crop('center', 'center', $width, $height);
        //}
        return $image;
    }

    public static function crop($image, $x, $y, $width, $height)
    {
        return $image->crop($x, $y, $width, $height);
    }

    public function save($path)
    {
        $this->image->saveToFile($path);
    }

    public static function getSizeFilename($size)
    {
        if (strstr($size, '.') === false) {
            $conf = CMS_Application::current()->config();
            $sizeFile = $conf['path'] . '/media/sizes/' . $size . '.pre';
        } else {
            $tmp = explode('.', $size);
            $component = CMS_Bazalt::getComponent($tmp[0]);
            $sizeFile = $component->BaseDir . '/media/sizes/' . $tmp[1] . '.pre';
        }
        if (!file_exists($sizeFile)) {
            throw new Exception('Invalid size file for size '.$size);
        }
        return $sizeFile;
    }

    public static function getThumb($image, $size = 'big')
    {
        if (empty($image)) {
            return null;
        }
        if (!$size) {
            throw new \Exception('Invalid size');
        }

        // temporary fix
        if (substr($image, 0, 5) == 'http:') {
            $image = relativePath(Assets_FileManager::copy($image));
        }

        $ext = strToLower(pathinfo($image, PATHINFO_EXTENSION));
        $filename = pathinfo($image, PATHINFO_FILENAME);
        if (!in_array($ext, self::$extensions)) {
            return null;
            //throw new Exception('Invalid extension "' . $ext . '" of file "' . $image . '"');
        }

        $cropImage = false;
        if ($size{0} == '[' && $size{strlen($size) - 1} == ']') {
            $size = substr($size, 1, -1);
            $cropImage = true;
        }
        

        $thumb = $filename . $size . $cropImage . '.' . $ext;

        $thumb = Bazalt::uploadFilename($thumb, 'thumb', true);
        $pre = $thumb . '.pre';

        // create precompiled file
        if (!is_file($pre)) {
            $sizes = explode('x', $size);
            $width = (int)$sizes[0];
            $height = (int)$sizes[1];

            $sizeData = [
                'format' => 'png',
                'file'   => $image
            ];

            $actions = [];
            $tr = [
                'method' => 'resize',
                'params' => [$width, $height]
            ];
            if ($cropImage) {
                $tr['params'] []= 'outside';
            }
            $actions []= $tr;
            if ($cropImage) {
                $tr = [
                    'method' => 'crop',
                    'params' => ['center', 'center', $width, $height]
                ];
                $actions []= $tr;
            } else if ($width != 0 && $height != 0) {
                $tr = [
                    'method' => 'transparentBackground',
                    'params' => [$width, $height]
                ];
                $actions []= $tr;
            }
            $sizeData->actions = $actions;

            if (file_put_contents($pre, json_encode($sizeData)) === false) {
                throw new \Exception('Cannot create pre file ' . $pre);
            }
        }
        return '/thumb.php?file=' . relativePath($thumb, SITE_DIR);
    }
}

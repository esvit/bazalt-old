<?php
using('Framework.System.Drawing');
using('Framework.System.Metadata');

class CMS_Image
{
    public static $extensions = array('jpg', 'jpeg', 'png', 'gif', 'bmp');

    protected $filename = null;

    protected $image = null;

    protected $actions = null;

    public function __construct($precompiled = null)
    {
        if ($precompiled != null) {
            if (is_string($precompiled)) {
                $precompiled = json_decode(file_get_contents($precompiled));
            }

            $this->actions = $precompiled->actions;
            $this->filename = PUBLIC_DIR . $precompiled->file;

            if (!file_exists($this->filename)) {
                throw new CMS_Exception_PageNotFound($this->filename);
            }
            $this->image = WideImage::load($this->filename);
        }
    }

    public static function createPrecompiled($file, $actions = array())
    {
        $pre = new stdClass;
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
        // temporary fix
        if (substr($image, 0, 5) == 'http:') {
            $image = relativePath(Assets_FileManager::copy($image));
        } else if (substr($image, 0, 3) != '/up') {
            $image = '/uploads' . $image;
        }

        if (strstr($size, 'x') !== false) {
            $sizes = explode('x', $size);
            $width = (int)$sizes[0];
            $height = (int)$sizes[1];
            if($width != 0 || $height != 0) {
                $sizeData = new stdClass;
                $sizeData->format = 'png';

                $actions = array();
                $tr = new stdClass;
                $tr->method = 'resize';
                $tr->params = array($width, $height);
                $actions []= $tr;
                if($width != 0 && $height != 0) {
                    $tr = new stdClass;
                    $tr->method = 'transparentBackground';
                    $tr->params = array($width, $height);
                    $actions []= $tr;
                }
                
                $sizeData->actions = $actions;
            }
        }
        // ----
        if (!$sizeData) {
            $sizeFile = self::getSizeFilename($size);

            $sizeData = json_decode(file_get_contents($sizeFile));
        }

        if (!$sizeData) {
            throw new Exception('Cannot read size file '.$sizeFile);
        }

        $ext = pathinfo($image, PATHINFO_EXTENSION);
        $filename = pathinfo($image, PATHINFO_FILENAME);
        if (!in_array(strToLower($ext), self::$extensions)) {
            throw new Exception('Invalid extension "' . $ext . '" of file "' . $image . '"');
        }

        if (isset($sizeData->format)) {
            $ext = $sizeData->format;
        }

        $thumb = $filename . $size . '.' . $ext;

        $thumb = Assets_FileManager::filename($thumb);
        $pre = $thumb . '.pre';

        // create precompiled file
        if (!file_exists($pre)) {
            $sizeData->file = $image;
            if (file_put_contents($pre, json_encode($sizeData)) === false) {
                throw new Exception('Cannot create pre file ' . $pre);
            }
        }
        return relativePath($thumb);
    }
}

<?php

namespace Components\Pages\Model;

use Framework\CMS as CMS;

class Image extends Base\Image
{
    public static function create()
    {
        $image = new Image();
        return $image;
    }
}
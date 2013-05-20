<?php

namespace Components\News\Model;

use Framework\CMS as CMS;

class ArticleImage extends Base\ArticleImage
{
    public function getThumb($size = 'big')
    {
        if ($size == 'real' || $size == 'original') {
            return $this->image;
        }
        return CMS\Image::getThumb($this->image, $size);
    }
}
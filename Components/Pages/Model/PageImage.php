<?php

class ComPages_Model_PageImage extends ComPages_Model_Base_PageImage
{
    public function getThumb($size = 'big')
    {
        if ($size == 'real' || $size == 'original') {
            return $this->image;
        }
        return CMS_Image::getThumb($this->image, $size);
    }
}
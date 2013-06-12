<?php

namespace Components\Gallery\Model;

use Framework\System\ORM\ORM,
    Framework\CMS as CMS,
    Framework\System\Routing\Route;

class Photo extends Base\Photo
{
    use CMS\ORM\LocalizableTrait;

    const MAX_WIDTH = 1024;

    const MAX_HEIGHT = 768;

    public function toArray()
    {
        $res = parent::toArray();

        $res['thumb'] = thumb($this->image, '250x160');
        return $res;
    }

    public function url()
    {
        return Route::urlFor('Gallery.Photo', array('album' => $this->Album->alias, 'photo' => $this->id));
    }

    public static function create()
    {
        $photo = new Photo();
        $photo->site_id = CMS\Bazalt::getSiteId();
        if (CMS\User::isLogined()) {
            $photo->user_id = CMS\User::get()->id;
        }
        return $photo;
    }

    public function getIcon()
    {
        $path = SITE_DIR . $this->icon;
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $pathCache = SITE_DIR . $this->icon . '_cache.' . $ext;

        if (!file_exists($path)) {
            return '';
        }

        if (!file_exists($pathCache)) {
            using('Framework.System.Drawing');

            $image = WideImage::load($path)->resize(self::THUMB_WIDTH, self::THUMB_HEIGHT);

            $deltaH = floor((self::THUMB_HEIGHT - $image->getHeight()) / 2) - 1;
            if ($deltaH < 0) {
                $deltaH = 0;
            }

            $img = WideImage_TrueColorImage::create(self::THUMB_WIDTH, self::THUMB_HEIGHT * 2);
            $transparentColor = $img->allocateColorAlpha(255, 255, 255, 127);
            $img->fill(0, 0, $transparentColor);
            $img->setTransparentColor($transparentColor);
            $img->merge($image, 'center', 'top + ' . $deltaH, 100)
                ->merge($image->asGrayscale(), 'center', self::THUMB_HEIGHT . ' + ' . $deltaH, 100)
                ->saveToFile($pathCache);
        }
        return $this->icon . '_cache.' . $ext;
    }

    public static function getCategoryCover(CMS_Model_Category $category)
    {
        $q = ORM::select()
                ->from('ComGallery_Model_Photo p')
//                ->leftJoin('ComGallery_Model_PhotoRefCategory ref', array('photo_id', 'p.id'))
//                ->where('ref.category_id = ?', $category->id)
                ->where('p.album_id = ?', $category->id)
                ->andWhere('p.site_id = ?', CMS_Bazalt::getSiteId())
//                ->andWhere('ref.is_cover = ?', 1)
                ;
        $photo = $q->fetch();

        if (!$photo) {
            $q = ORM::select()
                    ->from('ComGallery_Model_Photo p')
//                    ->leftJoin('ComGallery_Model_PhotoRefCategory ref', array('photo_id', 'p.id'))
//                    ->where('ref.category_id = ?', $category->id)
                    ->where('p.album_id = ?', $category->id)
                    ->andWhere('p.site_id = ?', CMS_Bazalt::getSiteId())
                    ->orderBy('p.order DESC')
                    ->limit(1);

            $photo = $q->fetch();

        }
        if ($photo) {
            return $photo;
        }
        return null;
    }

    public static function getCollection($album, $onlyPublished = true)
    {
        $q = ORM::select()
                ->from('Components\Gallery\Model\Photo p', 'p.*')
//                ->leftJoin('ComGallery_Model_PhotoRefCategory ref', array('photo_id', 'p.id'))
//                ->where('ref.category_id = ?', $category->id)
                ->where('p.album_id = ?', $album->id)
                ->orderBy('p.order DESC');
        if ($onlyPublished) {

        }
        return new CMS\ORM\Collection($q);
    }

    public static function getByCategoryIdCollection($categoryId)
    {
        $q = ORM::select()
                ->from('ComGallery_Model_Photo p', 'p.*')
//                ->leftJoin('ComGallery_Model_PhotoRefCategory ref', array('photo_id', 'p.id'))
//                ->where('ref.category_id = ?', $categoryId)
                ->where('p.album_id = ?', $categoryId)
                ->orderBy('p.order DESC');
        return new ORM_Collection($q);
    }

    public static function getList()
    {
        $q = ORM::select()
                ->from('ComGallery_Model_Photo p', 'p.*')
                ->where('p.site_id = ?', CMS_Bazalt::getSiteId())
                ->orderBy('p.order DESC');

        return $q->fetchAll();
    }

    public function getThumb($size = 'big')
    {
        $thumbs = unserialize($this->thumbs);
        if (is_array($thumbs) && isset($thumbs[$size])) {
           //return $thumbs[$size];
        }
        if (!is_array($thumbs)) {
            $thumbs = ['test' => 1];
        }
        $thumbs[$size] = CMS\Image::getThumb($this->image, $size);
        $this->thumbs = serialize($thumbs[$size]);
        $this->save();
        return $thumbs[$size];
    }

    public static function updatePhotoOrder($id, $order)
    {
        $q = ORM::update('Components\Gallery\Model\Photo')
                            ->set('order', $order)
                            ->where('id = ?', $id);

        $q->exec();
    }
}
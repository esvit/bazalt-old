<?php

namespace Components\Gallery\Model;

use Framework\System\ORM\ORM,
    Framework\CMS as CMS,
    Framework\System\Routing\Route;

class Album extends Base\Album
{
    public function url()
    {
        return Route::urlFor('Gallery.Album', ['album' => $this->alias]);
    }

    public function toArray()
    {
        $result = parent::toArray();
        $result['is_hidden'] = ($this->is_hidden == '1') ? true : false;
        $result['is_publish'] = ($this->is_publish == '1') ? true : false;
        $result['url'] = $this->url();
        return $result;
    }

    public static function create()
    {
        $album = new Album();
        $album->is_hidden = 0;
        $album->site_id = CMS\Bazalt::getSiteId();
        if (CMS\User::isLogined()) {
            $album->user_id = CMS\User::get()->id;
        }
        return $album;
    }

    public static function getRoot($siteId = null)
    {
        if ($siteId == null) {
            $siteId = CMS\Bazalt::getSiteId();
        }
        $q = ORM::select('Components\Gallery\Model\Album')
            ->where('depth = ?', 0)
            ->andWhere('site_id = ?', $siteId);

        $root = $q->fetch();
        if (!$root) {
            $root = new Album();
            $root->site_id = $siteId;
            $root->lft = 1;
            $root->rgt = 2;
            $root->depth = 0;
            $root->save();
        }
        return $root;
    }

    public static function getMain()
    {
        $q = ORM::select('ComGallery_Model_Album a', 'a.*, p.image')
            ->leftJoin('ComGallery_Model_Photo p', array('album_id', 'a.id'))
            ->where('a.site_id = ?', CMS\Bazalt::getSiteId())
            ->orderBy('a.lft');
        return $q->fetch(self::MODEL_NAME);
    }

    public static function getCollection($onlyPublished = true)
    {
        $q = ORM::select('Components\Gallery\Model\Album a', 'a.*, p.image')
            ->leftJoin('Components\Gallery\Model\Photo p', array('album_id', 'a.id'))
            //->where('a.lft BETWEEN ? AND ? AND a.depth = ?', array($album->lft + 1, $album->rgt - 1, $album->depth + 1))
            ->andWhere('a.site_id = ?', CMS\Bazalt::getSiteId())
            ->orderBy('a.id')
            ->groupBy('a.id');

        if ($onlyPublished) {
            $q->andWhere('a.is_hidden = ?', 0)
              ->andWhere('a.is_publish = ?', 1);
        }
        return new CMS\ORM\Collection($q);
    }

    public function getMaxOrder()
    {
        $q = ORM::select('Components\Gallery\Model\Photo p', 'MAX(p.order) AS `max`')
                ->where('p.album_id = ?', $this->id);

        $res = $q->fetch('stdClass');
        return ($res == null) ? 0 : $res->max;
    }

    public static function getPhotoCount($album)
    {
        $q = ORM::select('Components\Gallery\Model\Photo p', 'COUNT(*) AS `count`')
                ->where('p.album_id = ?', $album->id);
        $res = $q->fetch('stdClass');
        return ($res == null) ? 0 : $res->count;
    }

    public static function getThumb($album, $size = 'big') {
        if (!isset($album->image)) {
            return null;
        }
        return ComGallery::getThumb($album->image, $size);
    }

    public static function getByAlias($alias, $album = null) {
        $q = ORM::select('Components\Gallery\Model\Album a', 'a.*')
            ->where('a.site_id = ?', CMS\Bazalt::getSiteId())
            //->andWhere('a.depth > 0')
            ->andWhereGroup()
            ->orWhere('a.alias LIKE ?', $alias);

        /*if ($album != null) {
            if (is_numeric($album)) {
                $q->andWhere('a.album_id = ?', (int) $album);
            } elseif ($category instanceof ComGallery_Model_Album) {
                $q->andWhere('a.category_id = ?', $album->category_id);
                $q->andWhere('a.lft > ?', $album->lft);
                $q->andWhere('a.rgt < ?', $album->rgt);
            }
        }*/

        $q->endWhereGroup();
        return $q->limit(1)->fetch();
    }

    /**
     * Return category by path parts
     */
    public static function getByPath(array $parts, ComGallery_Model_Album $root = null)
    {
        if (!is_array($parts) || count($parts) == 0) {
            return null;
        }

        $nextElement = $root;
        foreach ($parts as $i => $part) {
            $qByAlias = ORM::select('ComGallery_Model_Album a', 'a.*')
                ->andWhere('a.alias = ?', $part)
                ->andWhere('a.is_publish = ?', 1)
                ->andWhere('a.depth > 0');

            if ($nextElement) {
                $qByAlias->andWhere('a.depth = ?', $nextElement->depth + 1)
                    ->andWhere('a.lft >= ? AND a.rgt <= ?', array($nextElement->lft, $nextElement->rgt))
                    ->andWhere('a.site_id = ?', $nextElement->site_id);
            }

            $nextElement = $qByAlias->fetch();
            if (!$nextElement) {
                return null;
            }
        }
        return $nextElement;
    }

}

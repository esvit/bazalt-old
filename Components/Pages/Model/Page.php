<?php

namespace Components\Pages\Model;

use Framework\CMS as CMS,
    Framework\System\ORM\ORM;
use Framework\System\Routing\Route;
use Framework\Core\Helper\Url;

class Page extends Base\Page
{
    public static function create()
    {
        $page = new Page();
        $page->site_id = CMS\Bazalt::getSiteId();
        return $page;
    }

    public static function getByUrl($url, $publish = null, $userId = null)
    {
        $q = Page::select()
            ->where('url = ?', $url)
            ->andWhere('f.site_id = ?', CMS\Bazalt::getSiteId());

        if ($publish != null) {
            $q->andWhere('publish = ?', $publish);
        }
        if ($publish == null && $userId != null) {
            $q->andWhere('user_id = ? OR publish = 1', $userId);
        }
        $q->limit(1);
        return $q->fetch();
    }

    public static function deleteByIds($ids)
    {
        if(!is_array($ids)) {
            $ids = array($ids);
        }
        $q = ORM::delete('Components\Pages\Model\Page a')
                ->whereIn('a.id', $ids)
                ->andWhere('a.site_id = ?', CMS\Bazalt::getSiteId());

        return $q->exec();
    }

    public function getCut()
    {
        $mPos = strpos($this->body, '<!-- pagebreak -->');
        if($mPos!== false) {
            return substr($this->body, 0, $mPos);
        }
        return $this->body;
    }

    public static function getCollection($published = null)
    {
        $q = ORM::select('Components\Pages\Model\Page f')
                //->innerJoin('ComPages_Model_PageLocale ref', array('id', 'f.id'))
                //->innerJoin('ComPages_Model_PageLocale ref', array('id', 'f.id'))
                //->where('ref.lang_id = ?', CMS_Language::getCurrentLanguage()->id)
                ->andWhere('f.site_id = ?', CMS\Bazalt::getSiteId());

        if ($published) {
            $q->andWhere('publish = ?', 1);
        }
        return new CMS\ORM\Collection($q);
    }

    public static function getCollectionByCategory($category, $published = null)
    {
        $q = ORM::select('ComPages_Model_Page f', 'f.*')
                ->innerJoin('ComPages_Model_Category c', array('id', 'f.category_id'))
                ->andWhere('c.lft >= ?', $category->lft)
                ->andWhere('c.rgt <= ?', $category->rgt)
                ->andWhere('c.site_id = ?', CMS\Bazalt::getSiteId())
                ->andWhere('f.site_id = ?', CMS\Bazalt::getSiteId())
                ->groupBy('f.id');

        if ($published) {
            $q->andWhere('publish = ?', 1);
        }
        return new CMS\ORM\Collection($q);
    }

    public function getUrl()
    {
        $url = $this->url;
        return Route::urlFor('ComPages.ShowByAlias', array('url' => $url));
    }
}

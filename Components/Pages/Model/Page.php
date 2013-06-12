<?php

namespace Components\Pages\Model;

use Framework\CMS as CMS,
    Bazalt\ORM,
    Framework\System\Routing as Routing;
use Framework\Core\Helper\Url;

class Page extends Base\Page implements Routing\Sluggable
{
    /**
     * Create new page without saving in database
     */
    public static function create()
    {
        $page = new Page();
        $page->site_id = CMS\Bazalt::getSiteId();
        return $page;
    }

    public function toUrl(Routing\Route $route)
    {
        return $this->url;
    }

    /**
     * Get page by url
     */
    public static function getByUrl($url, $is_published = null, $userId = null)
    {
        $q = Page::select()
                ->where('url = ?', $url)
                ->andWhere('f.site_id = ?', CMS\Bazalt::getSiteId());

        if ($is_published != null) {
            $q->andWhere('is_published = ?', $is_published);
        }
        if ($userId != null) {
            $q->andWhere('user_id = ?', $userId);
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

    public static function getCollection($is_publisheded = null, $category)
    {
        $q = ORM::select('Components\Pages\Model\Page f', 'f.*, ref.*')
                ->innerJoin('Components\Pages\Model\PageLocale ref', array('id', 'f.id'))
                ->where('ref.lang_id = ?', CMS\Language::getCurrentLanguage()->id)
                ->andWhere('f.site_id = ?', CMS\Bazalt::getSiteId());

        if ($is_publisheded) {
            $q->andWhere('is_published = ?', 1);
        }
        if ($category) {
            $q->andWhere('category_id = ?', $category->id);
        }
        return new CMS\ORM\Collection($q);
    }

    public function getUrl()
    {
        return Routing\Route::urlFor('Pages.Page', array('page' => $this));
    }

    public function toArray()
    {
        $res = parent::toArray();
        $res['is_published'] = $res['is_published'] == '1';

        $res['images'] = [];
        $images = $this->Images->get();
        foreach ($images as $image) {
            $res['images'][] = $image->toArray();
        }
        return $res;
    }
}

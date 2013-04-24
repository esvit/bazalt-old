<?php

class ComPages_Model_Page extends ComPages_Model_Base_Page
{
    public static function getByUrl($url, $publish = null, $userId = null)
    {
        $q = ComPages_Model_Page::select()
            ->where('url = ?', $url)
            ->andWhere('f.site_id = ?', CMS_Bazalt::getSiteId());

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
        $q = ORM::delete('ComPages_Model_Page a')
                ->whereIn('a.id', $ids)
                ->andWhere('a.site_id = ?', CMS_Bazalt::getSiteId());

        return $q->exec();
    }

    public function toArray()
    {
        $arr = parent::toArray();

        $arr['viewarticle'] = $this -> getUrl();

        $complete = array();
        $languages = array();
        $langs = CMS_Model_Language::getAll();
        foreach ($langs as $lang) {
            $languages[$lang->id] = $lang->alias;
        }
        $complArts = $this->getTranslations();
        foreach ($complArts as $article) {
            $langId = $article->lang_id;
            if ($langId) {
                $complete[$languages[$langId]] = $article->completed ? 2 : 1;
            }
        }
        foreach ($languages as $lang) {
            if (!array_key_exists($lang, $complete)) {
                $complete[$lang] = 0;
            }
        }
        $arr['langs'] = $complete;
        return $arr;
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
        $q = ORM::select('ComPages_Model_Page f')
                //->innerJoin('ComPages_Model_PageLocale ref', array('id', 'f.id'))
                //->innerJoin('ComPages_Model_PageLocale ref', array('id', 'f.id'))
                //->where('ref.lang_id = ?', CMS_Language::getCurrentLanguage()->id)
                ->andWhere('f.site_id = ?', CMS_Bazalt::getSiteId());

        if ($published) {
            $q->andWhere('publish = ?', 1);
        }
        return new CMS_ORM_Collection($q);
    }

    public static function getCollectionByCategory($category, $published = null)
    {
        $q = ORM::select('ComPages_Model_Page f', 'f.*')
                ->innerJoin('ComPages_Model_Category c', array('id', 'f.category_id'))
                ->andWhere('c.lft >= ?', $category->lft)
                ->andWhere('c.rgt <= ?', $category->rgt)
                ->andWhere('c.site_id = ?', CMS_Bazalt::getSiteId())
                ->andWhere('f.site_id = ?', CMS_Bazalt::getSiteId())              
                ->groupBy('f.id');

        if ($published) {
            $q->andWhere('publish = ?', 1);
        }
        return new CMS_ORM_Collection($q);
    }

    public function getUrl()
    {
        $url = $this->url;
        return CMS_Mapper::urlFor('ComPages.ShowByAlias', array('url' => $url));
    }

    public static function getList()
    {
        $q = ComPages_Model_Page::select();

        if (defined('ENABLE_MULTISITING') && ENABLE_MULTISITING) {
            $site = CMS_Bazalt::getSite();
            $q->andWhere('site_id = ?', $site->id);
        }
        $q->orderBy('`created_at` DESC');
        return $q->fetchAll();
    }

    public function save()
    {
        if (empty($this->url)) {
            $this->url = Url::cleanUrl(Locale_Config::getLocale()->translit($this->title));
        }
        parent::save();
    }
}

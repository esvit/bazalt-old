<?php

class CMS_Model_CategoryRoot extends CMS_Model_Base_CategoryRoot
{
    public static function create()
    {
        $category = new CMS_Model_CategoryRoot();
        $category->site_id = CMS_Bazalt::getSiteId();

        return $category;
    }

    public static function getByTitle($title)
    {
        $inAllLangs = CMS_Option::get(ComCategories::GET_BY_ALL_LANGUAGES_OPTION, true);
        if($inAllLangs) {
            $languages = ComI18N::getLanguages();
        } else {
            $languages = array(ComI18N::getCurrentLanguage());
        }
        $q = ORM::select('CMS_Model_CategoryRoot r');
        foreach ($languages as $lang) {
            $q->orWhere('r.title_' . $lang->alias . ' LIKE ?', $title);
        }
        return $q->fetch();
    }

    public static function getByAlias($alias)
    {
        $inAllLangs = CMS_Option::get(ComCategories::GET_BY_ALL_LANGUAGES_OPTION, true);
        if($inAllLangs) {
            $languages = ComI18N::getLanguages();
        } else {
            $languages = array(ComI18N::getCurrentLanguage());
        }
        $q = ORM::select('CMS_Model_CategoryRoot r')
                ->where('component_id IS NULL')
                ->andWhereGroup();
        foreach ($languages as $lang) {
            $q->orWhere('r.alias_' . $lang->alias . ' LIKE ?', $alias);
        }
        $q->endWhereGroup()
          ->limit(1);
        return $q->fetch();
    }

    public static function getElementByTitle($title)
    {
        $q = ORM::select('CMS_Model_Category r')
                ->innerJoin('CMS_Model_CategoryRoot ref', array('id', 'r.category_id'))
                ->innerJoin('CMS_Model_CategoryLocale l', array('id', 'r.id'))
                ->orWhere('l.title LIKE ?', $title)
                ->limit(1);

        return $q->fetch();
    }

    // public static function getElementByAlias($alias)
    // {
        // $q = ORM::select('CMS_Model_Category r')
           // ->where('r.alias_uk LIKE ? OR r.alias_ru LIKE ?', array($alias, $alias))
           //->where('r.alias_' . ComI18N::getCurrentLanguage()->alias . ' LIKE ?', $alias)
           //->andWhere('r.publish = ?', 1)
           // ->limit(1);

        // return $q->fetch();
    // }
    
    public static function getList($componentId = null)
    {
        $q = CMS_Model_CategoryRoot::select()
            ->where('(f.site_id = ? OR f.site_id IS NULL)', CMS_Bazalt::getSiteId())
            ->andWhereGroup()
            ->andWhere('component_id IS NULL');

        if ($componentId !== null) {
            $q->orWhere('component_id = ?', $componentId);
        }
        $q->endWhereGroup();

        return $q->fetchAll();
    }
    
    public static function getCollection($componentId = null)
    {
        $q = CMS_Model_CategoryRoot::select()
            ->where('(f.site_id = ? OR f.site_id IS NULL)', CMS_Bazalt::getSiteId());

        if ($componentId !== null) {
            $q->andWhere('component_id = ?', $componentId);
        }

        return new CMS_ORM_Collection($q);
    }

    public function save()
    {
        parent::save();

        $root = $this->Category;

        if (!$root) {
            $root = new CMS_Model_Category();

            $root->category_id = $this->id;
            $root->lft = 1;
            $root->rgt = 2;

            $root->save();
        }
        $this->Category = $root;
/*
        if (!isset($this->id)) {
            $q = new ORM_Query('SET FOREIGN_KEY_CHECKS = 0;');
            $q->exec();
        }

        $thisId = $this->id;
            
        if ($root != null) {
            $baseCategory = $root;
        } else {
            $baseCategory = new CMS_Model_Category();

            $baseCategory->lft = 1;
            $baseCategory->rgt = 2;
        }
        $baseCategory->category_id = $this->id;
        $baseCategory->save();

        if (!isset($thisId)) {
            $q = new ORM_Query('SET FOREIGN_KEY_CHECKS = 1;');
            $q->exec();
            
            $this->root_id = $baseCategory->id;
        }
        parent::save();*/
    }
}

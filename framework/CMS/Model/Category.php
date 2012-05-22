<?php

class CMS_Model_Category extends CMS_Model_Base_Category
{
    public static function create()
    {
        $category = new CMS_Model_Category();
        $categroy->site_id = CMS_Bazalt::getSiteId();

        return $category;
    }

    public function bindComponent($component = null)
    {
        $root = $this->Root;
        if ($component == null) {
            $root->component_id = null;
        } else {
            $root->component_id = $component->id;
        }
        $root->save();
    }

    public static function getByAlias($alias, $category = null, $componentId = null)
    {
        $q = ORM::select('CMS_Model_Category r', 'r.*')
                ->innerJoin('CMS_Model_CategoryRoot ref', array('id', 'r.category_id'))
                ->innerJoin('CMS_Model_CategoryLocale l', array('id', 'r.id'));

        if ($componentId == null) {
            $q->where('ref.component_id IS NULL');
        } else {
            $q->where('ref.component_id = ?', $componentId);
        }
        $q->andWhere('ref.site_id = ? OR ref.site_id IS NULL', CMS_Bazalt::getSiteId())->andWhereGroup();

        $q->orWhere('l.alias LIKE ?', $alias);

        if ($category != null) {
            if(is_numeric($category)) {
                $q->andWhere('r.category_id = ?', (int)$category);
            } elseif ($category instanceof CMS_Model_Category) {
                $q->andWhere('r.category_id = ?', $category->category_id);
                $q->andWhere('r.lft > ?', $category->lft);
                $q->andWhere('r.rgt < ?', $category->rgt);
            }
        }

        $q->endWhereGroup();
        return $q->limit(1)->fetch();
    }

    /**
     * Return category by path parts
     */
    public static function getByPath(array $parts, CMS_Model_Category $root = null)
    {
        if (!is_array($parts) || count($parts) == 0) {
            return null;
        }

        $langId = CMS_Language::getCurrentLanguage()->id;

        $nextElement = $root;
        foreach ($parts as $i => $part) {
            $qByAlias = ORM::select('CMS_Model_Category c', 'c.*, l.*')
                            ->innerJoin('CMS_Model_CategoryLocale l', array('id', 'c.id'))
                            //->where('l.lang_id = ?', $langId)
                            ->andWhere('l.alias = ?', $part)
                            ->andWhere('c.is_publish = ?', 1);

            if ($nextElement) {
                $qByAlias->andWhere('c.depth = ?', $nextElement->depth + 1)
                         ->andWhere('c.lft >= ? AND c.rgt <= ?', array($nextElement->lft, $nextElement->rgt))
                         ->andWhere('c.category_id = ?', $nextElement->category_id);
            }

            $nextElement = $qByAlias->fetch();
            if (!$nextElement) {
                return null;
            }
        }
        return $nextElement;
    }

    public function toArray()
    {
        $arr = parent::toArray();
        $arr['childrens_count'] = isset($this->Childrens) ? count($this->Childrens) : 0;

        return $arr;
    }
}

<?php

class ComNewsChannel_Widget_DependentNews extends CMS_Widget_Component
{
    public function fetch($vars)
    {
        $newsitem = ComNewsChannel::currentNews();
        if ($newsitem) {
            $category = $newsitem->Category;
            if ($category) {
                $this->view->assign('category', $category);
            }
            $news = ComNewsChannel_Model_Article::getCollectionByCategory($category, true);

            $ids = ComNewsChannel_Widget_RelatedNews::outputNews();
            $ids []= $newsitem->id;
            $news->andNotWhereIn('n.id', $ids)
                 ->limit(5);

            $this->view->assign('news', $news->fetchAll());
        }
        return parent::fetch();
    }

    public function getConfigPage($config)
    {
        return '';
    }

    public function getCategory()
    {
        if (isset($this->options['category_id'])) {
            $category = CMS_Model_Category::getById((int)$this->options['category_id']);
            return $category;
        }
        return null;
    }
}
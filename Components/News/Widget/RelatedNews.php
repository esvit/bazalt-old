<?php

class ComNewsChannel_Widget_RelatedNews extends CMS_Widget_Component
{
    protected static $outputNews = array();

    public static function outputNews()
    {
        return self::$outputNews;
    }

    public function fetch($vars)
    {
        $newsitem = ComNewsChannel::currentNews();
        if ($newsitem) {
            $category = isset($this->options['only_category']) ? $newsitem->Category : null;

            if ($category) {
                $this->view->assign('category', $category);
            }
            $news = $newsitem->getRelatedNews($category);

            foreach ($news as $item) {
                self::$outputNews []= $item->id;
            }
            $this->view->assign('news', $news);
        }
        return parent::fetch();
    }

    public function getConfigPage($config)
    {
        $this->view->assign('options', $this->options);
        return $this->view->fetch('widgets/related-news-setting');
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
<?php

class ComNewsChannel_Widget_VideoNews extends CMS_Widget_Component
{
    public function fetch($vars)
    {
        $news = ComNewsChannel_Model_Article::getVideoNews(true);
        $news = $news->getPage(1, 5);
        $this->view->assign('news', $news);

        return parent::fetch();
    }

    public function getConfigPage($config)
    {
        $this->view->assign('options', $this->options);
        return $this->view->fetch('widgets/related-news-setting');
    }
}
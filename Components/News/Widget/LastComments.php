<?php

class ComNewsChannel_Widget_LastComments extends CMS_Widget_Component
{
    public function fetch($config)
    {
        $comments = ComNewsChannel_Model_Comment::getLatestComments(10);
        $news = array();
        foreach ($comments as $comment) {
            if (!isset($news[$comment->news_id])) {
                $news[$comment->news_id] = $comment->Article;
                $news[$comment->news_id]->comments = array();
            }
            $arr = $news[$comment->news_id]->comments;
            $arr []= $comment;
            $news[$comment->news_id]->comments = $arr;
        }
        $this->view->assign('news', $news);

        return parent::fetch();
    }

    public function getConfigPage($config)
    {
        $refferers = array ('Select component','News comments');
        
        $this->view->assign('refferers', $refferers);
        $this->view->assign('options', $this->options);
        return $this->view->fetch('widgets/comments-settings');
    }

}
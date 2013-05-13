<?php

class ComNewsChannel_Dashboard_News extends Admin_Dashboard_Block
{
    public function getTitle()
    {
        $begin = strtotime('-1 month');
        $end = time();

        return __('News', ComNewsChannel::getName()) . ' ' . date('d.m.Y', $begin) . ' - ' . date('d.m.Y', $end);
    }

    public function getContent()
    {
        $begin = strtotime('-1 month +1 day');
        $end = time();

        $statistic = ComNewsChannel_Model_Article::getStatistic($begin, $end);

        foreach ($statistic as $i => $item) {
            $statistic[$i]->label = trim(Locale_Format::formatDate('%e %B %Y', strtotime($item->created_at)));
            if ($item->user_id) {
                $statistic[$i]->user_name = $item->User->getName();
            } else {
                $statistic[$i]->user_name = __('No author', ComNewsChannel::getName());
            }
        }
        $view = CMS_Bazalt::getComponent('ComNewsChannel')->View;
        $view->assign('statistic', $statistic);
        $view->assign('last', end($statistic));
        return $view->fetch('admin/statistic');
    }
}
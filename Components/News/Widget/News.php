<?php

class ComNewsChannel_Widget_News extends CMS_Widget_Component
{
    public function fetch($vars)
    {
        $quantity = (int)$this->options['quantity'];
        if (!$quantity || $quantity < 0 || $quantity > 100) {
            $quantity = 5;
        }
        $skip = (int)$this->options['skip'];
        if (!$skip || $skip < 0) {
            $skip = 0;
        }
        if (isset($this->options['region_id'])) {
            $region = ComGeo_Model_State::getById((int)$this->options['region_id']);
            if (!$region) {
                $this->options['region_id'] = null;
            }
            $this->view->assign('region', $region);
        }

        $category = $this->getCategory();
        if ($category) {
            $this->view->assign('category', $category);
        }
        $daysInTop = (int)CMS_Option::get(ComNewsChannel::NEWS_TOPDAYS_OPTION, 5);

        if (isset($this->options['by_region']) && $this->options['by_region']) {
            $collection = ComNewsChannel_Model_Article::getTopNews($daysInTop, $category, $this->options['region_id'], true, true);
            $collection->limit($skip, $quantity);

            $allNews = $collection->fetchAll();
            $this->view->assign('news', $allNews);

            $collection = ComNewsChannel_Model_Article::getTopNews($daysInTop, $category, 'world', true);
            $collection->limit($skip, $quantity);

            $allNews = $collection->fetchAll();
            $this->view->assign('news2', $allNews);
        } else {
            $collection = ComNewsChannel_Model_Article::getTopNews($daysInTop, $category, $this->options['region_id'], true);
            $collection->limit($skip, $quantity);

            $allNews = $collection->fetchAll();
            $this->view->assign('news', $allNews);
        }
        return parent::fetch();
    }

    public function getConfigPage($config)
    {
        $root = ComNewsChannel_Model_Category::getSiteRootCategory();
        $this->view->assign('tree', $root);
        $this->view->assign('category', $this->getCategory());

        $regions = ComNewsChannel_Model_Article::getRegions();
        $this->view->assign('regions', $regions);

        $this->view->assign('options', $this->options);
        return $this->view->fetch('widgets/settings/news');
    }

    public function getCategory()
    {
        if (isset($this->options['category_id'])) {
            $category = ComNewsChannel_Model_Category::getById((int)$this->options['category_id']);
            return $category;
        }
        return null;
    }
}
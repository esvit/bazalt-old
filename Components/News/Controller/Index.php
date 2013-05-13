<?php

namespace Components\News\Controller;

use Framework\CMS as CMS;
use Framework\System\Routing\Route;
use Components\News\Model\Category;
use Components\News\Model\Article;
use Framework\Core\Helper\Url;

class Index extends CMS\AbstractController
{
    public function newsAction($category = null, $region = null, $fullPath = false)
    {
        $news = Article::getCollection(true, $category, $region);

        //Metatags::set('PAGE_TITLE', __('News', ComNewsChannel::getName()));

        $this->view->assign('news', $news->getPage());

        $breadcrumb = CMS\Breadcrumb::root($newsitem->id);
        if ($region && $category) {
            $breadcrumb = $breadcrumb->insert(Route::urlFor('News.Region', ['region' => $region]), $region->title, explode("\n", $region->keywords));
        }
        if ($category && $category->depth > 1) {
            $breadcrumb = $category->Elements->getParent()->toBreadcrumb($breadcrumb, $region);
        }

        if (!$category && !$region) {
            $routeName = 'News.List';
        } else if (!$category && $region) {
            $routeName = 'News.Region';
        } else if ($category && !$region) {
            $routeName = 'News.Category';
        } else {
            $routeName = 'News.Region.Category';
        }

        $this->view->assign('pager', $news->getPager($routeName, ['category' => $category, 'region' => $region]));

        $this->view->assign('region', $region);
        $this->view->assign('category',  $category);

        $this->view->display('news/list');
    }

    public function photosAction()
    {
        $news = Article::getPhotoReports(true);

        //Metatags::set('PAGE_TITLE', __('News - Photo reports', ComNewsChannel::getName()));

        $this->view->assign('news', $news->getPage());
        $this->view->assign('pager', $news->getPager('News.PhotoNews'));

        $this->view->display('news/photos');
    }

    public function videoAction()
    {
        $news = Article::getVideoNews();

        //Metatags::set('PAGE_TITLE', __('News - Video', ComNewsChannel::getName()));

        $this->view->assign('news', $news->getPage());
        $this->view->assign('pager', $news->getPager('News.VideoNews'));

        $this->view->display('news/video');
    }

    public function viewAction($id, $category = null, $region = null, $fullPath = false, $url = null)
    {
        $newsitem = Article::getByIdAndSiteId($id);
        if (!$newsitem) {
            throw new CMS_Exception_PageNotFound();
        }
        $articleCategory = $newsitem->Category;
        $categoryUrl = $articleCategory->getUrl();

        $this->view->assign('region', $region);
        //Metatags::set('REGION_TITLE', $state->title);
        //Metatags::set('REGION2_TITLE', $state->title2);

        if ((!empty($region) && !$region) || ($newsitem->publish == 0 && $newsitem->user_id != CMS\User::get()->id)) {
            throw new CMS_Exception_PageNotFound();
        }

        if (CMS\Application::current()->url() != $newsitem->getUrl()) {
            Url::redirect($newsitem->getUrl());
        }
        \Components\News\Component::currentNews($newsitem);

        $breadcrumb = CMS\Breadcrumb::root($newsitem->id);
        if ($region) {
            $breadcrumb = $breadcrumb->insert(Route::urlFor('News.Region', ['region' => $region]), $region->title, explode("\n", $region->keywords));
        }
        if ($category) {
            $breadcrumb = $category->toBreadcrumb($breadcrumb, $region);
        }
        //$this->generateBreadcrumbs($region, $category, true);

        $collection = Article::getCollectionByCategory($articleCategory, true);
        $nextArticle = $collection->getNext($newsitem);
        $prevArticle = $collection->getPrev($newsitem);

        $this->view->assign('nextArticle', $nextArticle);
        $this->view->assign('prevArticle', $prevArticle);

        /*
        Metatags::Singleton()->title($newsitem->title);
        Metatags::set('NEWSITEM_TITLE', $newsitem->title);
        if (!empty($region)) {
            Metatags::set('REGION_TITLE', $state->title);
        } else {
            Metatags::set('REGION_TITLE', __('World'));
        }
        Metatags::set('NEWSITEM_DESCRIPTION', truncate($newsitem->body, 155));
        */
        // update hits, only if component ComTracking is enabled
        $hits = $this->view->get('tracking_hits');
        if (is_numeric($hits) && $newsitem->hits < $hits) {
            $newsitem->hits = $hits;
            $newsitem->save();
        }
        $this->view->assign('newsitem', $newsitem);
        if ($articleCategory == null) {
            $this->view->assign('backLink', CMS_Mapper::urlFor('ComNewsChannel.AllNews'));
        } else {
            //Metatags::set('NEWSCATEGORY_TITLE', $articleCategory->title);

            $this->view->assign('category', $articleCategory);
            $this->view->assign('backLink', Route::urlFor('News.Category', array('category' => $articleCategory)));
        }

        // admin bar on frontend
        $application = CMS\Application::current();
        /*if ($menu = $application->getGroup(CMS\Application\Web::GENERAL_GROUP)) {
            $menuitem = $menu->addItem(__('Edit news', ComNewsChannel::getName()), '/admin/ComNewsChannel/edit/' . $newsitem->id);
        }*/
        // Main page
        $this->view->display('news/article');
    }
    
    public function rssAction($category = null, $region = null, $fullPath = null)
    {
        $state = $this->getRegion($region);
        $this->view->assign('region', $state);
        $this->view->assign('category',  $category);

        $news = ComNewsChannel_Model_Article::getCollection(true, $category, $state);

        $news->andWhere('created_at > FROM_UNIXTIME(?)', strtotime('-3 day'))
             ->limit(50);

        $res = $news->fetchAll();
        foreach ($res as $i => $newsitem) {
            $res[$i]->created_at = strtotime($newsitem->created_at) + 3 * 3600;
        }
        //$countPerPage = CMS_Option::get(ComNewsChannel::NEWS_PAGECOUNT_OPTION, 20);
        $this->view->assign('news', $res);

        CMS_Response::noCache();
        CMS_Response::output($this->view->fetch('page.news_rss'), 'application/rss+xml; charset=UTF-8');
        exit;
    }

    public function tagAction($tag)
    {
        $tag = ComTags_Model_Tag::getByAlias($tag);
        if (!$tag) {
            throw new CMS_Exception_PageNotFound();
        }
        $news = ComNewsChannel_Model_Article::getCollectionByTag($tag, true);

        Metatags::set('PAGE_TITLE', __('News - Search by tag', ComNewsChannel::getName()));
        Metatags::set('TAG_TITLE', $tag->title);

        $countPerPage = 10;//CMS_Option::get(ComNewsChannel::NEWS_PAGECOUNT_OPTION, 10);
        $this->view->assign('news', $news->getPage(null, $countPerPage));
        $this->view->assign('pageCount', $news->getPagesCount());
        $this->view->assign('tag', $tag);

        $this->view->assign('pager', $news->getPager('ComNewsChannel.Tag', array('tag' => $tag->getAlias())));

        $this->view->display('page.news_tag');
    }
}
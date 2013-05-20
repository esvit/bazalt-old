<?php

namespace Components\News\Controller;

use Framework\CMS as CMS;
use Components\News\Model\Category;
use Components\News\Model\Article;

class Index extends CMS\AbstractController
{
    public function preAction($action, &$args)
    {
        parent::preAction($action, $args);
        unset($args['component']);

        //$this->component->addWebservice('ComNewsChannel_Webservice_News');
    }

    public function getRegion($region)
    {
        $region = trim($region);
        if (empty($region) || $region == '') {
            return null;
        }
        $state = '123';
        /*if ($region == 'world') {
            $region = ComGeo::getWorldRegion();
            $region->title = 'Світу';
            $region->title2 = 'Світу';
            return $region;
        }
        if ($region == 'vinnytsya') {
            $region = 'vinnytsia';
        }
        if ($region == 'kiev') {
            $state = ComGeo_Model_State::getById(32323);
        } else if ($region == 'lviv') {
            $state = ComGeo_Model_State::getById(36766);
        } else if ($region == 'ukraine') {
            $state = ComGeo_Model_State::getRoot(230);
            $state->title = 'Україна';
            $state->title2 = 'України';
        } else {
            $state = ComGeo_Model_State::getByAlias($region);
        }
        if (empty($state->title2)) {
            $state->title2 = $state->title;
        }*/
        return $state;
    }

    protected function generateBreadcrumbs($region, $category, $withLast = false)
    {
        $titles = array();
        if ($region) {
			switch ($region->id) {
			case 22491:
				$titles = array(
					0 => 'Последние новости Винницы',
					1 => 'Онлайн новости Винницы',
					2 => 'Новости города Винница',
					3 => 'Новости Винницы',
					4 => 'Свежие новости Винницы',
					5 => 'Останні новини Вінниці',
					6 => 'Онлайн новини Вінниці',
					7 => 'Новини міста Вінниця',
					8 => 'Новини м.Вінниця',
					9 => 'Свіжі новини Вінниці'
				);
				break;
			case 32323:
				$titles = array(
					0 => 'Последние новости Киева',
					1 => 'Онлайн новости Киева',
					2 => 'Новости города Киев',
					3 => 'Новости Киева',
					4 => 'Свежие новости Киева',
					5 => 'Останні новини Києва',
					6 => 'Онлайн новини Києва',
					7 => 'Новини міста Київ',
					8 => 'Новини м.Київ',
					9 => 'Свіжі новини Київа'
				);
				break;
			case 36766:
				$titles = array(
					0 => 'Последние новости Львова',
					1 => 'Онлайн новости Львова',
					2 => 'Новости города Львів',
					3 => 'Новости Львова',
					4 => 'Свежие новости Львова',
					5 => 'Останні новини Львова',
					6 => 'Онлайн новини Львова',
					7 => 'Новини міста Львів',
					8 => 'Новини м.Львів',
					9 => 'Свіжі новини Львова'
				);
				break;
			}
        }

        /*$breadcrumb = $this->view->breadcrumb();

        if ($region) {
            Metatags::set('REGION_TITLE', $region->title);
            if ($region && ($region->id == 22491 || $region->id == 36766)) {
                Metatags::set('REGION2_TITLE', ' у ' . $region->toCase(Locale_AbstractLanguage::PREPOSITIONAL_CASE));
            } else {
                Metatags::set('REGION2_TITLE', ' в ' . $region->toCase(Locale_AbstractLanguage::PREPOSITIONAL_CASE));
            }

            if ($category) {
                $url = CMS_Mapper::urlFor('ComNewsChannel.Region', array('region' => $region->alias));
                $breadcrumb = $breadcrumb->insert($url, $region->title, $titles);
            }
        }

        if ($category) {
            Metatags::set('NEWSCATEGORY_TITLE', $category->title);

            $path = $category->Elements->getPath();
            if ($withLast) {
                $path []= $category;
            }

            foreach ($path as $item) {
                if ($item->depth > 0) {
                    if ($region) {
                        $url = CMS_Mapper::urlFor('ComNewsChannel.Region.Category', array('region' => $region->alias, 'category' => $item->Elements));
                    } else {
                        $url = CMS_Mapper::urlFor('ComNewsChannel.ShowCategory', array('category' => $item->Elements));
                    }
                    $breadcrumb = $breadcrumb->insert($url, $item->title);
                }
            }
        }*/
        return $breadcrumb;
    }

    public function newsAction($category = null, $region = null, $fullPath = false)
    {
        $state = $this->getRegion($region);

        $news = Article::getCollection(true, $category, $state);

        //Metatags::set('PAGE_TITLE', __('News', ComNewsChannel::getName()));

        $countPerPage = 10;//CMS_Option::get(ComNewsChannel::NEWS_PAGECOUNT_OPTION, 10);
        $this->view->assign('news', $news->getPage(null, $countPerPage));
        $this->view->assign('pageCount',  $news->getPagesCount());

        $this->generateBreadcrumbs($state, $category);
        if (!$category && !$state->id) {
            $this->view->assign('pager', $news->getPager('News.List'));
        } else if (!$category && $state->id) {
            $this->view->assign('pager', $news->getPager('ComNewsChannel.Region', array('region' => $state->alias)));
        } else if ($category && !$state->id) {
            $this->view->assign('pager', $news->getPager('ComNewsChannel.ShowCategory', array('category' => $category->Elements)));
        } else {
            $this->view->assign('pager', $news->getPager('ComNewsChannel.Region.Category', array('region' => $state->alias, 'category' => $category->Elements)));
        }

        $this->view->assign('region', $state);
        $this->view->assign('category',  $category);

        $this->view->assign('rootCategory',  Category::getSiteRootCategory());
        $this->view->display('page.news');
    }

    public function photosAction()
    {
        $news = ComNewsChannel_Model_Article::getPhotoReports(true);

        Metatags::set('PAGE_TITLE', __('News - Photo reports', ComNewsChannel::getName()));

        $countPerPage = 10;//CMS_Option::get(ComNewsChannel::NEWS_PAGECOUNT_OPTION, 10);
        $this->view->assign('news', $news->getPage(null, $countPerPage));
        $this->view->assign('pageCount',  $news->getPagesCount());

        $this->view->assign('pager', $news->getPager('ComNewsChannel.Photos'));

        $this->view->display('page.news_photos');
    }

    public function videoAction()
    {
        $news = ComNewsChannel_Model_Article::getVideoNews();

        Metatags::set('PAGE_TITLE', __('News - Video', ComNewsChannel::getName()));

        $countPerPage = 10;//CMS_Option::get(ComNewsChannel::NEWS_PAGECOUNT_OPTION, 10);
        $this->view->assign('news', $news->getPage(null, $countPerPage));
        $this->view->assign('pageCount',  $news->getPagesCount());

        $this->view->assign('pager', $news->getPager('ComNewsChannel.Video'));

        $this->view->display('page.news_video');
    }

    public function editAction($id = null)
    {
        $news = ComNewsChannel_Model_Article::getPhotoReports(true);
        $user = CMS_User::getUser();
        if (!$user->hasRight(ComNewsChannel::getName(), ComNewsChannel::ACL_CAN_MANAGE_NEWS)) {
            throw new CMS_Exception_PageNotFound();
        }
        Metatags::set('PAGE_TITLE', __('News - Photo reports', ComNewsChannel::getName()));

        $countPerPage = 10;//CMS_Option::get(ComNewsChannel::NEWS_PAGECOUNT_OPTION, 10);
        $this->view->assign('news', $news->getPage(null, $countPerPage));
        $this->view->assign('pageCount',  $news->getPagesCount());

        $this->view->assign('pager', $news->getPager('ComNewsChannel.Photos'));

        $categories = ComNewsChannel_Model_Category::getSiteRootCategory();

        $this->view->assign('categories', $categories->Elements->get());


        $form = new ComNewsChannel_Form_PublicEdit();
        if (empty($id)) {
            if ($user->hasRight('ComNewsChannel', ComNewsChannel::ACL_CAN_CREATE_NEWS)){
                $newsitem = ComNewsChannel_Model_Article::create();
            } else {
                throw new CMS_Exception_PageNotFound();
            }
        } else {
            $newsitem = ComNewsChannel_Model_Article::getById(intval($id));
            $this->view->assign('newsitem', $newsitem);
        }

        $form->dataBind($newsitem);
        if ($form->isPostBack()) {
            if ($form['mainimage']->value()) {
                $file = $form['mainimage']->value();
                if (file_exists(PUBLIC_DIR . $file)) {
                    $img = CMS_Image::getThumb($file, '290x0');
                    $this->view->assign('img', $img);
                }
            }
            if ($form['category']->value()) {
                $category = $form['category']->value();
                $this->view->assign('category', $category);
            }
            if ( $form->validate()){
                $form->save();
                Url::redirect($form->dataBindedObject()->getUrl());
            }
        }
        $this->view->assign('form', $form);

        $this->view->display('page.news_edit');
    }

    public function viewAction($id, $category = null, $region = null, $fullPath = false, $url = null)
    {
        $newsitem = ComNewsChannel_Model_Article::getByIdAndSiteId($id);
        if (!$newsitem) {
            throw new CMS_Exception_PageNotFound();
        }
        $articleCategory = $newsitem->Category;
        $categoryUrl = $articleCategory->getUrl();

        $state = $this->getRegion($region);
        $this->view->assign('region', $state);
        Metatags::set('REGION_TITLE', $state->title);
        Metatags::set('REGION2_TITLE', $state->title2);

        if (is_string($category)) {
            $parts = explode('/', $category);
            $region = $parts[0];
            unset($parts[0]);
            $catUrl = implode('/', $parts);

            if ($catUrl != $categoryUrl && $categoryUrl != $category) {
                Url::redirect($newsitem->getUrl());
            }
        }

        if ((!empty($region) && !$state) || ($newsitem->publish == 0 && $newsitem->user_id != CMS_User::getUser()->id)) {
            throw new CMS_Exception_PageNotFound();
        }

        if (CMS_Application::current()->Url != $newsitem->getUrl()) {
            Url::redirect($newsitem->getUrl());
        }
        ComNewsChannel::currentNews($newsitem);

        $this->generateBreadcrumbs($state, $category, true);

        $collection = ComNewsChannel_Model_Article::getCollectionByCategory($articleCategory, true);
        $nextArticle = $collection->getNext($newsitem);
        $prevArticle = $collection->getPrev($newsitem);

        $this->view->assign('nextArticle', $nextArticle);
        $this->view->assign('prevArticle', $prevArticle);

        Metatags::Singleton()->title($newsitem->title);
        Metatags::set('NEWSITEM_TITLE', $newsitem->title);
        if (!empty($region)) {
            Metatags::set('REGION_TITLE', $state->title);
        } else {
            Metatags::set('REGION_TITLE', __('World'));
        }
        Metatags::set('NEWSITEM_DESCRIPTION', truncate($newsitem->body, 155));

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
            Metatags::set('NEWSCATEGORY_TITLE', $articleCategory->title);

            $this->view->assign('category', $articleCategory);
            $this->view->assign('backLink', CMS_Mapper::urlFor('ComNewsChannel.ShowCategory', array('category' => $articleCategory->Elements)));
        }

        // admin bar on frontend
        $application = CMS_Application::current();
        if ($menu = $application->getGroup(CMS_Application_Web::GENERAL_GROUP)) {
            $menuitem = $menu->addItem(__('Edit news', ComNewsChannel::getName()), '/admin/ComNewsChannel/edit/' . $newsitem->id);
        }
        // Main page
        $this->view->display('page.newsitem');
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
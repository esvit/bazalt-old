<?php

class ComNewsChannel_Controller_Company extends CMS_Component_Controller
{
    public function preAction($action, $args)
    {
        parent::preAction($action, $args);

        $this->component->addWebservice('ComNewsChannel_Webservice_News');
    }

    public function getRegion($region)
    {
        $region = trim($region);
        if (empty($region) || $region == '') {
            return null;
        }
        if ($region == 'world') {
            return false;
        }
        if ($region == 'vinnytsya') {
            $region = 'vinnytsia';
        }
        if ($region == 'ukraine') {
            $state = ComGeo_Model_State::getRoot(230);
            $state->title = 'Україна';
            $state->title2 = 'України';
        } else {
            $state = ComGeo_Model_State::getByAlias($region);
        }
        if (empty($state->title2)) {
            $state->title2 = $state->title;
        }
        return $state;
    }

    public function newsAction($companyAlias = null, $companyId = null, $region = null, $fullPath = false)
    {
        $company = ComEnterprise_Model_Company::getById((int)$companyId);
        if (!$company || $company->is_published == 0) {
            throw new CMS_Exception_PageNotFound();
        }

        $state = $this->getRegion($region);
        if (!$state) {
            throw new CMS_Exception_PageNotFound();
        }

        $this->view->assign('region', $state);
        Metatags::set('REGION_TITLE', $state->title);
        Metatags::set('REGION2_TITLE', $state->toCase(5));

        Metatags::set('PAGE_TITLE', __('News', ComNewsChannel::getName()));
        Metatags::set('COMPANY_TITLE', $company->title);

        $breadcrumb = $this->view->breadcrumb();
        $breadcrumb = $breadcrumb->insert(CMS_Mapper::urlFor('ComNewsChannel.Region', array('region' => $state->alias)), __('News of', ComNewsChannel::getName()) . ' ' . $state->toCase(5));

        $news = ComNewsChannel_Model_Article::getCollectionByCompany($company, true);

        $countPerPage = 10;//CMS_Option::get(ComNewsChannel::NEWS_PAGECOUNT_OPTION, 10);
        $this->view->assign('news', $news->getPage(null, $countPerPage));
        $this->view->assign('pageCount',  $news->getPagesCount());

        $this->view->assign('pager', $news->getPager('ComNewsChannel.CompanyNews', array('region' => $state->alias, 'companyAlias' => $company->getAlias(), 'companyId' => $company->id)));

        $this->view->assign('company',  $company);
        $this->view->assign('rootCategory',  ComNewsChannel_Model_Category::getSiteRootCategory());
        $this->view->display('page.news_company');
    }

    /**
     * @param $id
     * @param null $companyAlias
     * @param null $companyId
     * @param null $region
     * @param bool $fullPath
     * @param null $url
     * @throws CMS_Exception_PageNotFound
     */
    public function viewAction($id, $companyAlias = null, $companyId = null, $region = null, $fullPath = false, $url = null)
    {
        $newsitem = ComNewsChannel_Model_Article::getByIdAndCompanyId((int)$id, (int)$companyId);
        if (!$newsitem) {
            throw new CMS_Exception_PageNotFound();
        }
        $company = ComEnterprise_Model_Company::getById((int)$companyId);
        if (!$company || $company->is_published == 0) {
            throw new CMS_Exception_PageNotFound();
        }

        $state = $this->getRegion($region);
        if (!$state) {
            throw new CMS_Exception_PageNotFound();
        }

        $this->view->assign('region', $state);
        Metatags::set('REGION_TITLE', $state->title);
        Metatags::set('REGION2_TITLE', $state->toCase(5));

        if ($region == 'world') {
            $region = null;
        }
        if ((!empty($region) && !$state) || ($newsitem->publish == 0 && $newsitem->user_id != CMS_User::getUser()->id)) {
            throw new CMS_Exception_PageNotFound();
        }

        if (($newsitem->region_id && empty($region)) || ($newsitem->category_id && empty($category))) {
            Url::redirect($newsitem->getUrl());
        }
        ComNewsChannel::currentNews($newsitem);

        $breadcrumb = $this->view->breadcrumb();

        $collection = ComNewsChannel_Model_Article::getCollectionByCompany($company, true);
        $nextArticle = $collection->getNext($newsitem);
        $prevArticle = $collection->getPrev($newsitem);

        $this->view->assign('nextArticle', $nextArticle);
        $this->view->assign('prevArticle', $prevArticle);

        Metatags::Singleton()->title($newsitem->title);
        Metatags::set('NEWSITEM_TITLE', $newsitem->title);
        if (!empty($region)) {
            $breadcrumb = $breadcrumb->insert(CMS_Mapper::urlFor('ComNewsChannel.Region', array('region' => $region)), __('News of', ComNewsChannel::getName()) . ' ' . $state->toCase(5));

            $breadcrumb = $breadcrumb->insert(CMS_Mapper::urlFor('ComNewsChannel.CompanyNews', array('region' => $region,'companyAlias' => $company->getAlias(), 'companyId' => $company->id)), $company->title);

            Metatags::set('REGION_TITLE', $state->title);
        } else {
            Metatags::set('REGION_TITLE', __('World', ComNewsChannel::getName()));
        }
        Metatags::set('NEWSITEM_DESCRIPTION', truncate($newsitem->body, 155));

        // update hits, only if component ComTracking is enabled
        $hits = $this->view->get('tracking_hits');
        if (is_numeric($hits) && $newsitem->hits < $hits) {
            $newsitem->hits = $hits;
            $newsitem->save();
        }
        $this->view->assign('newsitem', $newsitem);

        // admin bar on frontend
        $application = CMS_Application::current();
        if ($menu = $application->getGroup(CMS_Application_Web::GENERAL_GROUP)) {
            $menuitem = $menu->addItem(__('Edit news', ComNewsChannel::getName()), '/admin/ComNewsChannel/edit/' . $newsitem->id);
        }
        $this->view->assign('company', $company);

        // Main page
        $this->view->display('page.newsitem_company');
    }
}

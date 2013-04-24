<?php

namespace Components\Pages;

use \Framework\CMS as CMS;
use Framework\System\Routing\Route;

class Component extends CMS\Component implements CMS\Menu\HasItems
{
    const ACL_HAS_ACCESS = 1;

    public static function getName()
    {
        return 'Pages';
    }

    public function getRoles()
    {
        return array(
            self::ACL_HAS_ACCESS => __('User can manage pages', __CLASS__)
        );
    }

    public function initComponent(CMS\Application $application)
    {
        $checkRegion = function($url, $name, $regionAlias, &$params) {
            $region = Model\Region::getByAlias($regionAlias);
            if ($region != null) {
                $params[$name] = $region;
                return true;
            }
            return false;
        };
        $checkCategory = function($url, $name, $categoryUrl, &$params) {
            $categories = explode('/', $categoryUrl);

            $lastCategory = null;
            if (count($categories) > 0) {
                $root = Model\Category::getSiteRootCategory();
                $lastCategory = Model\Category::getByPath($categories, $root);
            }
            if ($lastCategory) {
                $params[$name] = $lastCategory;
                return true;
            }
            return false;
        };
    
        $mapper = Route::root()->connect('News.List', '/news', ['component' => __CLASS__, 'controller' => 'Components\News\Controller\Index', 'action' => 'news']);

        $mapper->connect('News.PhotoNews', '/photos/', ['component' => __CLASS__, 'controller' => 'Components\News\Controller\Index', 'action' => 'photos']);

        $mapper->connect('News.VideoNews', '/video/', ['component' => __CLASS__, 'controller' => 'Components\News\Controller\Index', 'action' => 'video']);

        /*$mapper->connect('/new/', array('component' => __CLASS__, 'controller' => 'Default', 'action' => 'edit'))
                ->name('ComNewsChannel.FNew')
                ->noIndex()
                ->noFollow();

        $mapper->connect('/edit/{id:\d+}', array('component' => __CLASS__, 'controller' => 'Default', 'action' => 'edit'))
                ->name('ComNewsChannel.FEdit')
                ->noIndex()
                ->noFollow();*/

        $mapper->connect('News.Tag', '/tag-{tag}/', ['component' => __CLASS__, 'controller' => 'Components\News\Controller\Index', 'action' => 'tag']);

        // Subscribe page
        /*$mapper->connect('/subscribe/{id}/{code}', array('component' => __CLASS__, 'controller' => 'Subscribe', 'action' => 'default'))
               ->name('ComNewsChannel.SubscribePage')
               ->noIndex()->noFollow();
*/
        // Only region
        $regionMapper = Route::root()->connect('News.Region', '/{region}', ['component' => __CLASS__, 'controller' => 'Components\News\Controller\Index', 'action' => 'news', 'fullPath' => true]);
        $regionMapper->where('region', $checkRegion);
            //->condition('function', array('field' => 'region', 'component' => __CLASS__, 'function' => 'checkUrlRegion'));

        $regionMapper->connect('News.RSS.Region', '/rss.xml', ['component' => __CLASS__, 'controller' => 'Components\News\Controller\Index', 'action' => 'rss']);

        /*$companyMapper = $regionMapper->connect('News.CompanyList', '/{companyAlias}-{companyId:\d+}', array('component' => __CLASS__, 'controller' => 'Company', 'action' => 'news'))
            ->name('ComNewsChannel.CompanyNews');

        $companyMapper->connect('/{id:\d+}', array('component' => __CLASS__, 'controller' => 'Company','action' => 'view'))
            ->name('ComNewsChannel.CompanyArticle');*/

        $regionCategoryMapper = $regionMapper->connect('News.Region.Category', '/[category]', ['component' => __CLASS__, 'controller' => 'Components\News\Controller\Index', 'action' => 'news', 'fullPath' => true]);
        $regionCategoryMapper->where('region', $checkRegion);
        $regionCategoryMapper->where('category', $checkCategory);

        $regionCategoryMapper->connect('News.RSS.Region.Category', '/rss.xml', ['component' => __CLASS__, 'controller' => 'Components\News\Controller\Index', 'action' => 'rss']);

        $regionCategoryMapper->connect('News.Article.Region.Category', '/{id:\d+}', ['component' => __CLASS__, 'controller' => 'Components\News\Controller\Index', 'action' => 'view', 'fullPath' => true])
                             ->where('category', $checkCategory)
                             ->where('region', $checkRegion);
        ///

        $categoryMapper = Route::root()->connect('News.Category', '/[category]', ['component' => __CLASS__, 'controller' => 'Components\News\Controller\Index', 'action' => 'news', 'fullPath' => true]);
        $categoryMapper->where('category', $checkCategory);

        $mapper->connect('News.RSS', '/rss.xml', ['component' => __CLASS__, 'controller' => 'Components\News\Controller\Index', 'action' => 'rss']);

        $categoryMapper->connect('News.Article.Category', '/{id:\d+}', ['component' => __CLASS__, 'controller' => 'Components\News\Controller\Index', 'action' => 'view', 'fullPath' => true])
                       ->where('category', $checkCategory);

        $categoryMapper->connect('News.RSS.Category', '/rss.xml', ['component' => __CLASS__, 'controller' => 'Components\News\Controller\Index', 'action' => 'rss']);
    }

    public function getMenuTypes()
    {
        return array(
            'page' => 'ComPages_Menu_Page'
        );
    }
}

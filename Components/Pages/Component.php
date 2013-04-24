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

        $categoryMapper = Route::root()->connect('News.Category', '/[category]', ['component' => __CLASS__, 'controller' => 'Components\News\Controller\Index', 'action' => 'news', 'fullPath' => true]);
        $categoryMapper->where('category', $checkCategory);

        $categoryMapper->connect('News.Article.Category', '/{id:\d+}', ['component' => __CLASS__, 'controller' => 'Components\News\Controller\Index', 'action' => 'view', 'fullPath' => true])
                       ->where('category', $checkCategory);
    }

    public function getMenuTypes()
    {
        return array(
            'page' => 'ComPages_Menu_Page'
        );
    }
}

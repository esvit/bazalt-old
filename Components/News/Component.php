<?php

namespace Components\News;

use \Framework\CMS as CMS;
use Framework\System\Routing\Route;

/**
 * Компонент новин
 *
 * @category  ComNewsChannel
 * @package   BAZALT/Component
 * @copyright 2012 Equalteam
 * @license   GPLv3
 * @version   $Revision: 133 $
 */
class Component extends CMS\Component //implements CMS_Menu_HasItems, CMS_Interface_HasSitemap, CMS_Interface_HasNotifications, IEventable
{
    const ACL_HAS_ADMIN_ACCESS = 1;
    const ACL_CAN_CREATE_NEWS = 2;
    const ACL_CAN_MANAGE_NEWS = 4;
    const ACL_CAN_MANAGE_COMMENTS = 4;

    const NEWS_TOPDAYS_OPTION = 'ComNewsChannel.TopDays';

    const NEWS_BROADCAST_VK_GROUP_OPTION = 'ComNewsChannel.Broadcast.Group';

    const NEWS_PAGECOUNT_OPTION = 'ComNewsChannel.PageCount';

    const NEWS_CATEGORY_OPTION = 'ComNewsChannel.Category';

    const NEWS_SUBSCRIBED_ROLE_OPTION = 'ComNewsChannel.SubscribedRole';

    const LOG_CHANGE_ARTICLE = 'Log::ComNewsChannel::ChangeArticle';

    protected $newMenu;
    protected $editMenu;
    protected $commentsMenu;
    /**
     * Сюди записується поточна новина, якщо вона відображається
     * Змінна буде не null тільки на сторінках з 1 новиною
     *
     * @var ComNewsChannel_Model_Article
     */
    protected static $currentNews = null;

    public static function getName()
    {
        return 'News';
    }

    /**
     * @param ComNewsChannel_Model_Article|bool $newsitem
     * @return ComNewsChannel_Model_Article|null
     */
    public static function currentNews($newsitem = false)
    {
        if ($newsitem !== false) {
            self::$currentNews = $newsitem;
        }
        return self::$currentNews;
    }

    public function getRoles()
    {
        return array(
            self::ACL_HAS_ADMIN_ACCESS => __('User can have access to admin news section', __CLASS__),
            self::ACL_CAN_CREATE_NEWS => __('User can create news', __CLASS__),
            self::ACL_CAN_MANAGE_NEWS => __('User can manage news section', __CLASS__),
            self::ACL_CAN_MANAGE_COMMENTS => __('User can manage comments', __CLASS__)
        );
    }

    public function getLogActions()
    {
        return array(
            self::LOG_CHANGE_ARTICLE => __('User {user_login} changed news {title}', __CLASS__)
        );
    }

    public function initComponent(CMS\Application $application)
    {
        if ($application instanceof \App\Site\Application) {
            $application->registerJsComponent('Component.News', relativePath(__DIR__ . '/component.js'));
        } else {
            $application->registerJsComponent('Component.News.Admin', relativePath(__DIR__ . '/admin.js'));
        }

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
/*
    public function initBackend(CMS_Application $backend)
    {
        if (!$this->hasRight(self::ACL_HAS_ADMIN_ACCESS)) {
            return;
        }
        // init routes
        $map = $backend->getMapper(__CLASS__);

        $map->connect('/', array(
                    'controller' => 'Admin',
                    'action' => 'default'))
                ->name('ComNewsChannel.List');

        $map->connect('/category/{categoryId}/', array(
                    'controller' => 'Admin',
                    'action' => 'defaultByCategory'))
                ->name('ComNewsChannel.ListByCategory');

        $map->connect('/new', array(
                    'controller' => 'Admin',
                    'action' => 'edit'))
                ->name('ComNewsChannel.New');

        $map->connect('/edit/{id}', array(
                    'controller' => 'Admin',
                    'action' => 'edit'))
                ->name('ComNewsChannel.Edit');

        $map->connect('/comments', array(
                    'controller' => 'Admin',
                    'action' => 'comments'))
                ->name('ComNewsChannel.Comments');

        $map->connect('/settings/', array(
                    'controller' => 'Admin',
                    'action' => 'settings'))
                ->name('ComNewsChannel.Settings');

        // init menu
        $menu = $backend->getGroup(Admin_App::GENERAL_GROUP)
                ->addItem(__('News', __CLASS__), $this->urlFor('ComNewsChannel.List'), $this)
                ->addOption('icon-class', 'menu-image-ComNewsChannel');

        $user = CMS_User::getUser();

        $this->editMenu = $menu->addItem(__('Edit', __CLASS__), $this->urlFor('ComNewsChannel.List'), $this);
        if ($user->hasRight('ComNewsChannel', ComNewsChannel::ACL_CAN_CREATE_NEWS)) {
            $this->newMenu = $menu->addItem(__('Add new', __CLASS__), $this->urlFor('ComNewsChannel.New'), $this);
        }
        $this->commentsMenu = $menu->addItem(__('Comments', __CLASS__), $this->urlFor('ComNewsChannel.Comments'), $this);
        $backend->addDashboardBlock(new ComNewsChannel_Dashboard_News());

        $backend->SettingsSubmenu->addItem(__('News', ComNewsChannel::getName()), $this->urlFor('ComNewsChannel.Settings'), $this);

        $this->addStyle('css/admin.css');
    }

    public function getMenuTypes()
    {
        return array(
            'news' => 'ComNewsChannel_Menu_News',
            'category' => 'ComNewsChannel_Menu_NewsCategory'
        );
    }

    public function getSitemaps()
    {
        return array(
            'news' => new ComNewsChannel_Sitemap_News(),
            'categories' => new ComNewsChannel_Sitemap_Categories(),
            'archive' => new ComNewsChannel_Sitemap_Archive(),
            //'tags' => new ComNewsChannel_Sitemap_Tags()
        );
    }

    public function getEvents()
    {
        return array(
            'OnUserSubscribe' => array(
                'title' => __('On user subscribe', self::getName()),
                'variables' => array(
                    'login',
                    'username',
                    'usermail',
                    'sitehost',
                    'subscribeCode',
                    'subscribeLink'
                )
            ),
            'OnGuestSubscribe' => array(
                'title' => __('On guest subscribe', self::getName()),
                'variables' => array(
                    'usermail',
                    'sitehost',
                    'subscribeCode',
                    'subscribeLink'
                )
            )
        );
    }
*/
}

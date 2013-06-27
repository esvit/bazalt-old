<?php

namespace Components\Shop;

use \Framework\CMS as CMS,
    \Framework\System\Routing\Route;

class Component extends CMS\Component implements CMS\Menu\HasItems
{
    protected static $productCategory = null;

    protected static $shop = null;

    public static function getName()
    {
        return 'Shop';
    }

    public static function currentCategory($productCategory = null)
    {
        if ($productCategory !== null) {
            self::$productCategory = $productCategory;
        }
        return self::$productCategory;
    }

    public static function currentShop($shop = null)
    {
        if ($shop !== null) {
            self::$shop = $shop;
        }
        if (!self::$shop) {
            self::$shop = Model\Shop::select()->fetch();
        }
        return self::$shop;
    }

    public function initComponent(CMS\Application $application)
    {
        if ($application instanceof \App\Site\Application) {
          //  $application->registerJsComponent('Component.Shop', relativePath(__DIR__ . '/component.js'));
	    $application->view()->assign('wishListCount', WishList::getCountForUser(CMS\User::getUser()));
        } else {
            $application->registerJsComponent('Component.Shop.Admin', relativePath(__DIR__ . '/admin.js'));
        }

        $controller = 'Components\Shop\Controller\Index';

        $shop = null;
        // Shops
        $checkShop = function($url, $name, $shopId, &$params) use (&$shop) {
            $shop = Model\Shop::getById((int)$shopId);
            if ($shop) {
                //$params[$name] = $shop;
                self::currentShop($shop);
                return true;
            }
            return false;
        };
        $shopRoute = Route::root()->connect('Shop.Shops', '/shop',                 ['component' => self::getName(), 'controller' => $controller, 'action' => 'default'])
                     ->connect('Shop.Shop',       '/{shopId}',        ['component' => self::getName(), 'controller' => $controller, 'action' => 'shop'])
                     ->where('shopId', $checkShop);

        // Product
        $productMapper = $shopRoute->connect('Shop.Product', '/{product}-{productId:\d+}', ['component' => self::getName(), 'controller' => 'Components\Shop\Controller\Index', 'action' => 'product']);
        $productMapper->where('shopId', $checkShop);

        // Categories
        $checkCategory = function($url, $name, $categoryUrl, &$params) use ($shop) {
            $categories = explode('/', $categoryUrl);

            $lastCategory = null;
            if (count($categories) > 0) {
                $root = Model\Category::getShopRootCategory($shop);
                $lastCategory = Model\Category::getByPath($categories, $root);
            }
            if ($lastCategory) {
                $params[$name] = $lastCategory;
                return true;
            }
            return false;
        };
        $categoryMapper = $shopRoute->connect('Shop.Category', '/[category]', ['component' => self::getName(), 'controller' => 'Components\Shop\Controller\Index', 'action' => 'category', '_fullPath' => true]);
        $categoryMapper->where('shopId', $checkShop);
        $categoryMapper->where('category', $checkCategory);
    }

    public function getMenuTypes()
    {
        return [
        ];
    }
}
<?php

namespace Components\Menu\Model;

use Framework\CMS as CMS;

class Element extends Base\Element
{
    protected $menuItem = null;

    public static function create($menuId = null)
    {
        $item = new Element();
        $item->site_id = CMS\Bazalt::getSiteId();
        $item->root_id = $menuId;
        $item->lft = 1;
        $item->rgt = 2;
        $item->is_publish = false;
        return $item;
    }

    public function toArray()
    {
        $res = parent::toArray();
        $res['is_publish'] = $this->is_publish == 1;
        unset($res['Childrens']);
        $elements = $this->Elements->get();
        $count = 0;
        $toArray = function($items) use (&$toArray, &$count) {
            $result = [];
            foreach ($items as $key => $item) {
                $count++;
                $res = $item->toArray();
                $res['children'] = (is_array($item->Childrens) && count($item->Childrens)) ? $toArray($item->Childrens) : [];
                $result[$key] = $res;
            }
            return $result;
        };
        $res['children'] = $toArray($elements);
        $res['count'] = $count;
        if (!$res['config']) {
            $res['config'] = new \stdClass();
        }
        return $res;
    }

    public static function getRoots()
    {
        $q = Element::select()
                    ->where('depth = ?', 0)
                    ->andWhere('site_id = ?', CMS\Bazalt::getSiteId());

        return $q->fetchAll();
    }

    public static function getComponentMenuType($componentName, $menuType, Element $element = null)
    {
        $component = CMS\Bazalt::getComponent($componentName);

        if (!$component || !($component instanceof CMS\Menu\HasItems)) {
            return null;
            //throw new \Exception('Component "' . $componentName . '" must implements Framework\CMS\Menu\HasItems interface');
        }

        $menuTypes = $component->getMenuTypes();
        if (!isset($menuTypes[$menuType]) || !class_exists($menuClass = $menuTypes[$menuType])) {
            return null;
            //throw new \Exception('Menu type not found in component');
        }
        $menuItem = new $menuClass($component, $element);
        if (!($menuItem instanceof CMS\Menu\ComponentItem)) {
            throw new \Exception('Menu type must be instance of Framework\CMS\Menu\ComponentItem');
        }
        return $menuItem;
    }

    public static function getElementById($id, $menuId)
    {
        $q = Element::select()
                    ->where('id = ?', (int)$id)
                    ->andWhere('root_id = ?', (int)$menuId);

        return $q->fetch();
    }

    public function getTitle($lang)
    {
        $menuItem = $this->getTranslation($lang);
        if (!$menuItem) {
            return null;
        }
        return $menuItem->title;
    }

    public function getDescription($lang)
    {
        $menuItem = $this->getTranslation($lang);
        if (!$menuItem) {
            return null;
        }
        return $menuItem->description;
    }

    public function getUrl()
    {
        if (array_key_exists('url', $this->config)) {
            return $this->config['url'];
        }
        return '/';
    }

    public function isEmptyMenu()
    {
        return ($this->getMenuItem() == null);
    }

    public function getMenuType()
    {
        $menuItem = $this->getMenuItem();
        if ($menuItem) {
            return $menuItem->getItemType();
        }
        return null;
    }

    public function getMenuItem()
    {
        if (!$this->menuType || !($component = $this->Component)) {
            return null;
        }
        if (!$this->menuItem) {
            $this->menuItem = self::getComponentMenuType($component->name, $this->menuType, $this);
        }
        return $this->menuItem;
    }

    public static function getByComponentName($componentName)
    {
        $q = ORM::select()
            ->from('Components\Menu\Model\Element els')
            ->innerJoin('Framework\CMS\Model\Component c', array('id', 'els.component_id'))
            ->where('c.name = ?', $componentName);

        return $q->fetchAll();
    }

    public function getSettingsForm()
    {
        $view = CMS\View::root();
        $view->assign('menuitem', $this);
        $settings = $view->fetch('menu/standart_settings');

        $menuItem = $this->getMenuItem();
        if ($menuItem) {
            $settings .= $menuItem->getSettingsForm();
        }
        return $settings;
    }
    

    public function getMenu($onlyPublish = true)
    {
        $addItemsToMenu = function(&$menu, $elements) use (&$addItemsToMenu)
        {
            if (!is_array($elements)) {
                return;
            }
            foreach ($elements as $menuitem) {
                $item = $menuitem->getMenuItem();

                if ($item != null) {
                    $item->prepare();
                    if($item->visible()) {
                        if (count($menuitem->Childrens) > 0) {
                            $addItemsToMenu($item, $menuitem->Childrens);
                        }
                        $menu->addMenuItem($item);
                    }
                }
            }
        };

        $menu = new CMS\Menu\Item();
        if ($onlyPublish) {
            $elements = $this->PublicElements->get();
        } else {
            $elements = $this->Elements->get();
        }
        $addItemsToMenu($menu, $elements);

        return $menu;
    }

    /**
     * Get menu types of all loaded components
     *
     * @throws \Exception
     * @return array Array of menu types
     */
    public static function getMenuTypes()
    {
        $menuTypes = array();
        $components = CMS\Bazalt::getComponents();

        $num = 0;
        foreach ($components as $component) {
            if ($component instanceof CMS\Menu\HasItems) {
                $compMenus = $component->getMenuTypes();
                $config = $component->config();

                foreach ($compMenus as $key => $menuClass) {
                    if (!class_exists($menuClass)) {
                        continue;
                    }
                    $menuItem = new $menuClass($component);
                    if (!($menuItem instanceof CMS\Menu\ComponentItem)) {
                        throw new \Exception(sprintf('Menu must "%s" be instance of Framework\CMS\Menu\ComponentItem', $menuClass));
                    }
                    $menuTypes[$num++] = $menuItem;
                }
            }
        }
        return $menuTypes;
    }
}

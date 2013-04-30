<?php
/**
 * Data model
 *
 * @category DataModel
 * @package  DataModel
 * @author   DataModel Generator v1.3.2 <tools@bazalt.org.ua>
 * @license  http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version  SVN: $Id: commenumenus.class.inc 108 2010-09-13 06:17:26Z esvit $
 * @revision SVN: $Rev: 108 $
 * @link     http://bazalt.org.ua/
 */

/**
 * Data model for table "com_menu_menus"
 *
 * @category DataModel
 * @package  DataModel
 * @author   DataModel Generator v1.3.2 <tools@bazalt.org.ua>
 * @license  http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link     http://bazalt.org.ua/
 *
 * @property-read mixed Id
 * @property-read mixed Title
 */
class ComMenu_Model_Menu extends ComMenu_Model_Base_Menu
{
    /**
     * Create new menu
     *
     * @return ComMenu_Model_Menu
     */
    public static function create()
    {
        $item = new ComMenu_Model_Menu();
        $item->site_id = CMS_Bazalt::getSiteId();

        return $item;
    }

    public static function getSiteMenus()
    {
        $q = ComMenu_Model_Menu::select()
                ->andWhere('site_id = ?', CMS_Bazalt::getSiteId());

        return $q->fetchAll();
    }

    /**
     * Save menu item
     * If menu root element is null - create it
     */
    public function save()
    {
        parent::save();

        if (empty($this->root_id)) {
            $root = new ComMenu_Model_Element();
            $root->menu_id = $this->id;
            $root->lft = 1;
            $root->rgt = 2;
            $root->save();

            $this->root_id = $root->id;
            $this->save();
        }
    }

    /**
     * Get menu types of all loaded components
     *
     * @return array Array of menu types
     */
    public function getMenuTypes()
    {
        $menuTypes = array();
        $menus = Type::filterByInterface(Type::getDeclaredClasses(), 'CMS_Menu_HasItems');

        foreach ($menus as $component) {
            $comp = CMS_Bazalt::getComponent($component);
            $compMenus = $comp->getMenuTypes();
            $cmsComponent = $comp->CmsComponent;

            foreach ($compMenus as $key => $menuClass) {
                if (!class_exists($menuClass)) {
                    continue;
                    //throw new Exception(sprintf('Class "%s" not found', $menuClass));
                }
                $menuItem = new $menuClass($comp);
                if (!($menuItem instanceof CMS_Menu_ComponentItem)) {
                    throw new Exception(sprintf('Menu must "%s" be instance of CMS_Menu_ComponentItem', $menuClass));
                }
                $menuTypes[$cmsComponent->title][$key] = $menuItem;
            }
        }
        return $menuTypes;
    }
}

<?php

class CMS_Menu extends Object
{
    protected $items = array();

    protected $css = '';

    protected $count = 0;

    public function setCss($value)
    {
        return $this->css($value);
    }

    public function addCss($css)
    {
        $this->css .= ' ' . $css;
        return $this;
    }

    public function addItem($title, $url = '', $data = null)
    {
        return $this->addMenuItem(new CMS_Menu_Item($title, $url, $data));
    }

    public function addMenuItem(CMS_Menu_Item &$item)
    {
        $item->menuId = ++$this->count;
        $this->items []= $item;
        return $item;
    }

    public function removeItem(CMS_Menu_Item $item)
    {
        foreach ($this->items as $key => $menuitem) {
            if ($item->menuId == $menuitem->menuId) {
                unset($this->items[$key]);
            }
        }
    }

    public function setCurrentMenuByUrl($url)
    {
        foreach ($this->items as $item) {
            $item->setCurrentMenuByUrl($url);
        }
    }

    public function hasItem($title)
    {
        foreach ($this->items as $item) {
            if ($item->title == $title) {
                return $item;
            }
        }
        return null;
    }

    public function hasActiveMenu()
    {
        foreach ($this->items as $item) {
            if ($item->IsActive) {
                return true;
            }
        }
        return false;
    }

    public function hasSubMenu()
    {
        return (@count($this->items) > 0);
    }

    public function addSeparator()
    {
        $this->items []= new CMS_Menu_Item('-', '-');
    }

    public function getItems()
    {
        if (count($this->items) > 0) {
            $lastItem = end($this->items);
            $firstItem = reset($this->items);
            $firstItem->addCss('menu-first-item');
            $lastItem->addCss('menu-last-item');
        }
        return $this->items;
    }

    public function getCss()
    {
        return $this->css();
    }

    public function css($value = null)
    {
        if ($value != null) {
            $this->css = $value;
            return $this;
        }

        $css = explode(' ', $this->css);
        if ($this->hasSubMenu()) {
            $css []= 'menu-has-submenu';
        }
        if ($this->hasActiveMenu()) {
            $css []= 'menu-has-submenu-active';
        }
        $css = array_unique($css);
        return trim(implode(' ', $css));
    }
}

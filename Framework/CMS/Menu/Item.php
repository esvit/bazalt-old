<?php

namespace Framework\CMS\Menu;

use Framework\Core\Helper\Url;

class Item
{
    protected $menuId;

    protected $title;
    
    protected $visible = true;

    protected $description;

    protected $url;

    protected $id;

    protected $data = array();

    protected $isActive = false;

    protected $options = array();
    
    protected $items = array();

    protected $css = '';

    protected $target = null;

    protected $count = 0;

    public function __construct($title = null, $url = null, $data = array())
    {
        $this->title = $title;
        $this->url = $url;
        $this->data = $data;
        $this->isActive = ($url == Url::getRequestUrl(true));
    }

    public function addOption($name, $value)
    {
        $this->options[$name] = $value;
        return $this;
    }

    public function hasOption($name)
    {
        return array_key_exists($name, $this->options);
    }

    public function getOption($name)
    {
        if ($this->hasOption($name)) {
            return $this->options[$name];
        }
        return null;
    }

    public function removeOption($name)
    {
        if ($this->hasOption($name)) {
            unset($this->options[$name]);
        }
    }

    public function isActive()
    {
        if ($this->isActive) {
            return true;
        }
        return $this->hasActiveMenu();
    }

    public function getIsActive()
    {
        return $this->isActive();
    }

    public function activate($active = true)
    {
        $this->isActive = $active;
    }

    public function setCurrentMenuByUrl($url)
    {
        $currUrl = $this->getUrl();
        if (!empty($currUrl) && $currUrl{strlen($currUrl) - 1} != '/') {
            $currUrl .= '/';
        }
        if ($url == $currUrl) {
            $this->isActive = true;
        }
        foreach ($this->items as $item) {
            $item->setCurrentMenuByUrl($url);
        }
    }

    public function getUrl($params = null)
    {
        if ($params != null) {
            $url = new DataType_Url($this->url);
            $url->setParams($params);
            return $url->toString();
        }
        return $this->url;
    }

    public function url($url = null)
    {
        if ($url != null) {
            $this->url = $url;
            return $this;
        }
        return $this->url;
    }

    public function title($title = null)
    {
        if ($title !== null) {
            $this->title = $title;
            return $this;
        }
        return $this->title;
    }

    public function id($id = null)
    {
        if ($id !== null) {
            $this->id = $id;
            return $this;
        }
        return $this->id;
    }

    public function target($target = null)
    {
        if ($target !== null) {
            $this->target = $target;
            return $this;
        }
        return $this->target;
    }

    public function description($description = null)
    {
        if ($description !== null) {
            $this->description = $description;
            return $this;
        }
        return $this->description;
    }

    public function visible($visible = null)
    {
        if ($visible !== null) {
            $this->visible = $visible;
            return $this;
        }
        return $this->visible;
    }

    public function getTitle()
    {
        return $this->title;
    }


    public function css($value = null)
    {
        if ($value != null) {
            return $this->css = $value;
        }

        $css = explode(' ', $this->css);
        if ($this->url == '-') {
            $css []= 'menu-separator';
        } else {
            $css []= 'menu-item';
        }
        if ($this->isActive) {
            $css []= 'menu-item-active';
        }
        if (is_array($this->data) && isset($this->data['css'])) {
            $css []= $this->data['css'];
        }
        $css = array_unique($css);
        return trim(implode(' ', $css));
    }

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
        return $this->addMenuItem(new Item($title, $url, $data));
    }

    public function addMenuItem(Item &$item)
    {
        $item->menuId = ++$this->count;
        $this->items []= $item;
        return $item;
    }

    public function removeItem(Item $item)
    {
        foreach ($this->items as $key => $menuitem) {
            if ($item->menuId == $menuitem->menuId) {
                unset($this->items[$key]);
            }
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
        $this->items []= new Item('-', '-');
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
}

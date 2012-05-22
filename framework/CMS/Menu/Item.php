<?php

class CMS_Menu_Item extends CMS_Menu
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
        parent::setCurrentMenuByUrl($url);
    }

    public function getUrl()
    {
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
            return parent::css($value);
        }

        $css = explode(' ', parent::css());
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
}

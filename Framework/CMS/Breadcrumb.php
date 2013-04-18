<?php

namespace Framework\CMS;

use Framework\System\Routing\Route;

class Breadcrumb
{
    protected $nextCrumb = null;

    protected $prevCrumb = null;

    protected $url = null;

    protected $title = null;

    protected $isActive = null;

    protected $variants = array();

    protected static $variation = 1;

    protected static $root = null;

    public static function root($variation = null, $title = null)
    {
        if ($variation !== null) {
            self::$variation = $variation;
        }
        if (!self::$root) {
            if (!$title) {
                $title = 'Home';
            }
            self::$root = new Breadcrumb(Route::urlFor('home'), $title);
            View::root()->assign('breadcrumbs', self::$root);
        }
        return self::$root;
    }

    protected function __construct($url, $title = null, $isActive = false, $variants = array())
    {
        $this->url = $url;
        $this->title = $title;
        $this->isActive = $isActive;
        $this->variants = $variants;
    }

    public function insert($url, $title, $variants = array())
    {
        $breadcrumb = new Breadcrumb($url, $title, false, $variants);
        $this->nextCrumb = $breadcrumb;
        $breadcrumb->prevCrumb = $this;

        return $breadcrumb;
    }

    public function title($title = null)
    {
        if ($title !== null) {
            $this->title = $title;
            return $this;
        }
        if (count($this->variants) > 0) {
            $last = self::$variation % count($this->variants);

            if (!empty($this->variants[$last])) {
                return $this->variants[$last];
            }
        }
        return $this->title;
    }

    public function url($url = null)
    {
        if ($url !== null) {
            $this->url = $url;
            return $this;
        }
        return $this->url;
    }

    public function isActive($isActive = null)
    {
        if ($isActive !== null) {
            $this->isActive = $isActive;
            return $this;
        }
        return $this->isActive;
    }

    public function next(Breadcrumb $nextCrumb = null)
    {
        if ($nextCrumb !== null) {
            $this->nextCrumb = $nextCrumb;
            return $this;
        }
        return $this->nextCrumb;
    }

    public function prev($prevCrumb = null)
    {
        if ($prevCrumb !== null) {
            $this->prevCrumb = $prevCrumb;
            return $this;
        }
        return $this->prevCrumb;
    }
}
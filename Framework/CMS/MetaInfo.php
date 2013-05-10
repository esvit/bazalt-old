<?php

namespace Framework\CMS;

class MetaInfo
{
    protected static $title = '';
    protected static $keywords = '';
    protected static $description = '';

    protected $route = null;

    protected $vars = [];

    public function __construct(Route $route)
    {
        $this->route = $route;
    }

    public static function title($title = null)
    {
        if ($title !== null) {
            self::$title = $title;
        }
        return self::$title;
    }

    public static function keywords($keywords = null)
    {
        if ($keywords !== null) {
            self::$keywords = $keywords;
        }
        return self::$keywords;
    }

    public static function description($description = null)
    {
        if ($description !== null) {
            self::$description = $description;
        }
        return self::$description;
    }

    public function assign($name, $value)
    {
        $this->vars[$name] = $value;
    }

    public function toString()
    {
        $info = $this->toTemplate();

        $vars = [];
        foreach ($this->vars as $name => $value) {
            $vars[$name] = htmlentities($value, ENT_QUOTES, "UTF-8");
        }

        $meta = '<title>' . $info['title'] . '</title>';
        $meta .= '<meta name="keywords" content="" />';
        $meta .= '<meta name="description" content="" />';
        $meta .= '<meta name="seo_vars" content=\'' . json_encode($vars) . '\' />';
        $meta .= '<meta name="route_name" content="' . $this->route->name() . '" />';

        return $meta;
    }

    public function toTemplate()
    {
        $title = trim(self::title());
        $title = empty($title) ? Bazalt::getSite()->title : $title;

        $meta = [
            'title' => $title,
            'keywords' => self::keywords(),
            'description' => self::description()
        ];
        foreach ($meta as $name => $value) {
            $meta[$name] = View\TwigEngine::fetchString($value, $this->vars);
        }
        return $meta;
    }
}
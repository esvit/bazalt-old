<?php

namespace Framework\CMS;

if (!defined('USE_DEFAULT_THEME')) {
    define('USE_DEFAULT_THEME', true);
}

class View extends \Bazalt\View
{
    const DEFAULT_LAYOUT = 'layout';

    protected $component = null;

    /**
     * Linked list of breadcrumbs
     *
     * @var CMS_Breadcrumb
     */
    protected $breadcrumb = null;

    protected static $layout = self::DEFAULT_LAYOUT;

    // refactore it
    public static $headAppendString = '';

    protected static $addScripts = true;

    /**
     * Default layout file
     */
    public static function getLayout()
    {
        return self::$layout;
    }

    public static function addScripts($addScripts)
    {
        self::$addScripts = $addScripts;
    }

    public static function setLayout($layout)
    {
        self::$layout = $layout;
    }

    public function breadcrumb()
    {
        if (!$this->breadcrumb) {
            $this->breadcrumb = new CMS_Breadcrumb(CMS_Mapper::urlFor('home'));
            $this->assignByRef('pageBreadcrumb', $this->breadcrumb);
        }
        return $this->breadcrumb;
    }

    public static function getVar($name)
    {
        $view = Application::current()->View;
        return isset($view->AssignedVars[$name]) ? $view->AssignedVars[$name] : null;
    }

    protected function __construct($folders = [])
    {
        $locale = [
            'CMS' => CMS_DIR . '/views'
        ];
        if (!is_array($folders)) {
            $folders = [];
        }
        $folders = array_merge($locale, $folders);

        parent::__construct($folders);
    }

    /*public function __construct($folders = array(), $component = null)
    {
        $locale = array(
            'CMS'              => CMS_DIR . '/templates'
        );
        if (!is_array($folders)) {
            $folders = array();
        }
        $folders = array_merge($locale, $folders);

        // add application view
        $conf = CMS_Application::current()->config();
        $folders[CMS_Application::current()->name() . '_App'] = $conf['path'] . '/templates';
        if (USE_THEMES) {
            if (USE_DEFAULT_THEME) {
                $folders[CMS_Theme::DEFAULT_NAME] = CMS_Theme::getDefaultTheme()->getPath() . '/templates';
            }

            $theme = CMS_Theme::getCurrentTheme();
            if ($theme != null && $theme->alias != CMS_Theme::DEFAULT_NAME) {
                $folders[$theme->alias] = $theme->getTemplatesPath();
            }
        }

        parent::__construct($folders);

        $this->component = $component;
    }*/

    public function fetch($template, $vars = null)
    {
        if ($this->component != null) {
            $this->assign('component', $this->component);
        }
        return parent::fetch($template, $vars);
    }

    public function getPage($template = null)
    {
        if ($template == null) {
            $template = self::$layout;
        }
        $content = $this->fetch($template);

        $content = $this->replaceTags($content);
        return $content;
    }

    protected function replaceTags($content)
    {
        /*if (!self::$addScripts) {
            return $content;
        }
        $head = Assets_CSS::getHtml() . "\n";
        $head .= Metatags::Singleton()->__toString() . "\n";

        if (!empty(self::$headAppendString)) {
            $head .= self::$headAppendString;
        }
        $afterBody = '<script>' . CMS_Bazalt::generateJavascript() . '</script>';
        $afterBody .= Assets_JS::getHtml() . "\n";
        $afterBody .= Scripts::getInlineScript();

        // add favicon
        $theme = CMS_Theme::getCurrentTheme();
        $icon = CMS_Theme::getFaviconTag($theme->getPath(), $theme->getInfo());
        $head .= $icon;*/

        $components = Application::current()->jsComponents();
        if (!count($components)) {
            $components = new \stdClass();
        }
        $head .= '<script>';
        $head .= 'var components = ' . json_encode($components);
        $head .= '</script>';

        $content = str_replace('<head>',  '<head>' . $head, $content);
        $content = str_replace('</body>', $afterBody . '</body>', $content);

        return $content;
    }

    public function showPage($template, &$content = null, Route $route = null)
    {
        if ($route && $info = $route->getMetaInfo()) {
            $meta = $info->toString();
            $this->assign('_meta', $info->toTemplate());
        }
        //CMS_Theme::addMetadata();
        if (strpos($_SERVER['HTTP_ACCEPT'], 'application/json') === 0) {
            Response::output($meta . '<div class="ng-view-container">' . $content . '</div>');
            exit;
        }

        if ($content !== null) {
            $this->assign('content', $content);
        }
        if (self::$layout === null) {
            $page = $this->replaceTags($content);
        } else {
            $page = $this->getPage(self::$layout);
        }

        Response::output($page);
    }
}

View::engine('php', new View\PHPEngine());
View::engine('twg', new View\TwigEngine());
View::engine('inc', new View\PHPEngine());
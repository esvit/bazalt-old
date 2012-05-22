<?php

using('Framework.System.View');

class CMS_View extends View_Base
{
    const DEFAULT_LAYOUT = 'layout';

    protected $component = null;

    protected static $layout = self::DEFAULT_LAYOUT;

    // refactore it
    public static $headAppendString = '';

    /**
     * Default layout file
     */
    public static function getLayout()
    {
        return self::$layout;
    }

    public static function setLayout($layout)
    {
        self::$layout = $layout;
    }

    public static function getVar($name)
    {
        $view = CMS_Application::current()->View;
        return isset($view->AssignedVars[$name]) ? $view->AssignedVars[$name] : null;
    }
    
    public function __construct($folders = array(), $component = null)
    {
        $locale = array(
            'CMS_Bazalt'              => CMS_DIR . '/templates'
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
                $folders[CMS_Theme::DEFAULT_NAME] = CMS_Theme::getThemeTemplatePath();
            }

            $theme = CMS_Theme::getCurrentTheme();
            if ($theme != null && $theme->Alias != CMS_Theme::DEFAULT_NAME) {
                $folders[$theme->Alias] = $theme->getTemplatesPath();
            }
        }

        parent::__construct($folders);

        $this->component = $component;
    }

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

        $head = Assets_CSS::getHtml() . "\n";
        $head .= Metatags::Singleton()->__toString() . "\n";

        if (!empty(self::$headAppendString)) {
            $head .= self::$headAppendString;
        }
        $afterBody = Assets_JS::getHtml() . "\n";
        $afterBody .= Scripts::getInlineScript();

        $content = str_replace('</head>', $head . '</head>', $content);
        $content = str_replace('</body>', $afterBody . '</body>', $content);

        return $content;
    }

    public function showPage($template, &$content = null)
    {
        $theme = CMS_Theme::getCurrentTheme();

        if ($theme != null && USE_DEFAULT_THEME) {
            $theme->addMetadata();
        }

        if ($content !== null) {
            $this->assign('content', $content);
        }
        $page = $this->getPage(self::$layout);

        CMS_Response::output($page);
    }
}
<?php

class CMS_Theme extends Object
{
    /**
     * Name of theme options in database
     */
    const THEME_OPTION = 'CMS.Theme';

    /**
     * Default theme name
     */
    const DEFAULT_NAME = 'default';

    /**
     * Template dir name
     */
    const TEMPLATES_DIRNAME = 'templates';

    /**
     * Filname with theme info
     */
    const THEME_FILENAME = 'theme.xml';

    /**
     * Default theme info
     */
    protected static $defaultTheme = null;

    /**
     * Name of current theme
     */
    protected static $name;

    /**
     * Current theme info
     */
    protected static $currentTheme = null;

    /**
     * Event trigger when set new theme
     */
    public $eventOnThemeSet = Event::EMPTY_EVENT;
    
    /**
     * Event trigger when get theme info
     */
    public $eventOnGetThemeInfo = Event::EMPTY_EVENT;

    const FAVICON_PATTERN = '<link rel="icon" href="%s" type="%s" />';

    const SHORTCUT_PATTERN = '<link rel="shortcut icon" href="%s" type="%s" />';

    protected $title;

    protected $alias;

    protected $description = 'No description';

    protected $author = null;

    protected $authorEmail = null;

    protected $thumbnail = null;

    protected $screenshot = null;

    protected $path = null;

    protected $favicon = null;

    protected $faviconType = null;

    protected $styles = array();

    protected $scripts = array();

    protected $jslibs = array();

    public function getFaviconTag()
    {
        if (!empty($this->favicon)) {
            if (empty($this->faviconType)) {
                $info = pathinfo($this->getPath() . '/' . $this->favicon);
                switch ($info['extension']) {
                case 'ico': $this->faviconType = 'image/x-icon'; break; //image/vnd.microsoft.icon
                case 'png': $this->faviconType = 'image/png'; break;
                case 'gif': $this->faviconType = 'image/gif'; break;
                default:
                    throw new Exception('Unknown favicon mime type. Set mime type in attribute "favicon-type"');
                }
            }
            return sprintf(self::FAVICON_PATTERN, $this->favicon, $this->faviconType) . "\n" .
                   sprintf(self::SHORTCUT_PATTERN, $this->favicon, $this->faviconType);
        }
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getUrlPath()
    {
        return relativePath($this->path, SITE_DIR);
    }

    public function getTemplatesPath()
    {
        return $this->getPath() . PATH_SEP . CMS_Theme::TEMPLATES_DIRNAME;
    }

    public function getTemplates()
    {
        $ret = array();
        $files = glob(self::getTemplatesPath() . '/*.tpl');
        $files = array_merge($files, glob(self::getTemplatesPath() . '/*.twg'));

        foreach ($files as $file) {
            $cacheKey = 'getTemplates' . $file;
            $templ = Cache::Singleton()->getCache($cacheKey);

            if (!$templ) {
                $templ = array(
                    'name' => basename($file),
                    'title' => basename($file),
                    'file' => $file
                );
                $templ = array_merge($templ, self::getTemplateInfo($file));
            }
            if (!empty($templ['title'])) {
                Cache::Singleton()->setCache($cacheKey, $templ);
                $ret [] = $templ;
            }
        }
        return $ret;
    }

    protected static function getTagsAttributes($attr)
    {
        preg_match_all("/([a-z0-9_]+)\s*=\s*[\"\']([^\"\']*)[\"\']/xsi", $attr, $matches, PREG_SET_ORDER);
        $attrs = array();
        foreach ($matches as $match) {
            $attrs[$match[1]] = $match[2];
        }
        return $attrs;
    }

    protected static function getTemplateInfo($file)
    {
        $ldq = preg_quote('{');
        $rdq = preg_quote('}');
        $cmd = preg_quote('template');

        $content = @file_get_contents($file);

        if (empty($content)) {
            return;
        }

        preg_match_all("/\{template(.*)}/", $content, $matches);
        $ret = self::getTagsAttributes($matches[1][0]);
        $ret['positions'] = array();

        preg_match_all("/\{widgets(.*)\}/", $content, $matches);
        foreach($matches[1] as $match) {
            $ret['positions'] []= self::getTagsAttributes($match);
        }

        preg_match_all("/\{%\s*widgets(.*)\s*%\}/", $content, $matches);
        foreach($matches[1] as $match) {
            $match = trim($match, '"\' ');
            $ret['positions'] []= array('position' => $match);//self::getTagsAttributes($match);
        }
        return $ret;
    }

    public function __construct($filename)
    {
        $file = XmlParser::parse($filename);

        $this->alias = basename(dirname($filename));

        $this->path = dirname($filename);

        $this->parseMetaInfo($file);
        $this->parseStylesInfo($file);
        $this->parseScriptsInfo($file);
        $this->parseJsLibsInfo($file);
        $this->parseLayoutInfo($file);
    }

    protected function parseLayoutInfo($node)
    {
        $layout = $node->node('layout');
        if (!$layout) {
            return;
        }

        $this->favicon = $layout->attribute('favicon');
        $this->faviconType = $layout->attribute('favicon-type');
    }

    public function printFaviconTag()
    {
        echo $this->getFaviconTag();
    }

    protected function parseStylesInfo($node)
    {
        $styles = $node->node('styles');
        if (!$styles) {
            return;
        }
        foreach ($styles as $style) {
            $condition = $style->attribute('condition');
            if (empty($condition)) {
                $condition = Assets_FileManager::NO_CONDITION;
            }
            $this->styles []= array(
                'file'      => $style->value(),
                'condition' => $condition
            );
        }
    }

    protected function parseJsLibsInfo($node)
    {
        $jslibs = $node->node('jslibs');
        if (!$jslibs) {
            return;
        }
        foreach ($jslibs as $jslib) {
            $this->jslibs []= $jslib->value();
        }
    }

    protected function parseScriptsInfo($node)
    {
        $scripts = $node->node('scripts');
        if (!$scripts) {
            return;
        }
        foreach ($scripts as $script) {
            $this->scripts []= $script->value();
        }
    }

    protected function parseMetaInfo($node)
    {
        $meta = $node->node('meta');
        if (!$meta) {
            return;
        }
        $title = $meta->node('title');

        if (!$title || $title->value() == '') {
            throw new Exception('Theme file must have meta/title tag');
        }
        $this->title = $title->value();

        $description = $meta->node('description');
        if ($description) {
            $this->description = $description->value();
        }

        $author = $meta->node('author');
        if ($author) {
            $this->author = $author->value();
        }

        $authorEmail = $meta->node('author_email');
        if ($authorEmail) {
            $this->authorEmail = $authorEmail->value();
        }

        $thumbnail = $meta->node('thumbnail');
        if ($thumbnail) {
            $this->thumbnail = $thumbnail->value();
        }

        $screenshot = $meta->node('screenshot');
        if ($screenshot) {
            $this->screenshot = $screenshot->value();
        }
    }

    public function getThumbnail()
    {
        if (empty($this->thumbnail)) {
            return false;
        }
        $file = SITE_DIR . $this->getUrlPath() . PATH_SEP . $this->thumbnail;
        if (!file_exists($file)) {
            return false;
        }
        $publicFile = Assets_FileManager::copy($file);
        return relativePath($publicFile, PUBLIC_DIR);
    }

    public static function getThemeInfo($alias)
    {
        $filename = null;

        Event::trigger(__CLASS__, 'OnGetThemeInfo', array($alias, &$filename));
        if ($filename == null) {
            $filename = THEMES_DIR . PATH_SEP . $alias . PATH_SEP . self::THEME_FILENAME;
        }

        if (!file_exists($filename)) {
            throw new CMS_Exception_Theme('Theme "' . $alias . '" not found ("' . $filename . '")');
        }

        return new CMS_Theme($filename);
    }

    /**
     * Return current theme name
     */
    public static function getCurrentTheme()
    {
        if (self::$currentTheme == null) {
            try {
                self::setCurrentTheme(CMS_Option::get(self::THEME_OPTION, self::DEFAULT_NAME));
            } catch (CMS_Exception_Theme $e) {
                self::setCurrentTheme(self::DEFAULT_NAME);
            } catch (CMS_Exception_DomainNotFound $ex) {
                // ignore if this is not register domain
            }
        }
        return self::$currentTheme;
    }

    /**
     * Return list of avaliable themes
     */
    public static function getAllThemes($path = null)
    {
        if (empty($path)) {
            $path = THEMES_DIR;
        }
        $themes = array();
        foreach (glob($path . PATH_SEP . '*' . PATH_SEP . self::THEME_FILENAME) as $filename) {
            $info = new CMS_Theme($filename);
            $themes[$info->Alias] = $info;
        }
        return $themes;
    }

    /**
     * Set current theme name
     */
    public static function setCurrentTheme($name)
    {
        self::$currentTheme = self::getThemeInfo($name);
        self::$name = $name;

        //CMS_Application::current()->View->assignByRef('cms_theme_name', self::$name);
        //CMS_Application::current()->View->assignByRef('cms_theme', self::$currentTheme);

        Event::trigger(__CLASS__, 'OnThemeSet', array(self::$currentTheme));
    }

    /**
     * Return path to current theme templates
     */
    public static function getTemplatePath($templateName, $searchInDefaultTheme = true)
    {
        $rInfo = explode(':', $templateName, 2);
        $handler = array_shift($rInfo);
        if (count($rInfo) > 0) {
            $templateName = array_shift($rInfo);
        }
        $template = '';

        # Themes
        if (self::getCurrentTheme() != null) {
            $path = self::getCurrentTheme()->getTemplatesPath();
            $template = $path . PATH_SEP . $templateName;

            if (file_exists($template)) {
                return $path;
            }
        }

        if (USE_DEFAULT_THEME && self::$defaultTheme == null) {
            self::$defaultTheme = self::getThemeInfo(self::DEFAULT_NAME);
        }

        # Default theme
        if (USE_DEFAULT_THEME && $searchInDefaultTheme && self::$defaultTheme != null) {
            $path = self::$defaultTheme->getTemplatesPath();
            $template = $path . PATH_SEP . $templateName;

            if (file_exists($template)) {
                return $path;
            } else {
                # Template path
                $path = self::getTemplatesPath();
                $template = $path  . PATH_SEP . $templateName;
                if (file_exists($template)) {
                    return $path;
                }
            }
        }
        return null;
    }

    public static function getThemeTemplatePath($themeName = self::DEFAULT_NAME)
    {
        return self::getThemeInfo($themeName)->getTemplatesPath();
    }

    public static function getCurrentThemeTemplatePath()
    {
        return self::getCurrentTheme()->getTemplatesPath();
    }

    public static function getThemePath($themeName = self::DEFAULT_NAME)
    {
        return self::getThemeInfo($themeName)->getPath();
    }

    public static function setTemplatesPath($path)
    {
        Theme::Singleton()->getAdapter()->setTemplateDir($path);
    }

    public function addStyle($file, $name = null, $condition = null)
    {
        if (substr($file, 0, 4) == 'http') {
            $path = $file;
        } else {
            if (file_exists($this->getPath() . '/styles/' . $file)) {
                $path = $this->getPath() . '/styles/' . $file;
            } else {
                $path = $this->getPath() . '/' . $file;
            }
        }
        Assets_CSS::add($path, $condition);
    }

    public function addScript($file, $name = null)
    {
        if (substr($file, 0, 4) == 'http') {
            $path = $file;
        } else {
            if (file_exists($this->getPath() . '/scripts/' . $file)) {
                $path = $this->getPath() . '/scripts/' . $file;
            } else {
                $path = $this->getPath() . '/' . $file;
            }
        }
        Scripts::add($path, $name);
    }

    public function addMetadata()
    {
        Event::register('Hooks', 'html_head_end', array($this, 'printFaviconTag'));

        // add theme js libs
        foreach ($this->Jslibs as $jslib) {
            Scripts::addModule($jslib);
        }
        // add theme styles
        foreach ($this->Styles as $style) {
            $this->addStyle($style['file'], null, $style['condition']);
        }
        // add theme scripts
        foreach ($this->Scripts as $script) {
            $this->addScript($script);
        }
    }
}
<?php

if (!defined('WIDGET_BORDER_AROUND')) {
    define('WIDGET_BORDER_AROUND', true);
}

abstract class CMS_Widget extends Object
{
    protected $widgetConfig;

    protected $options = array();

    protected $view;

    public function getConfigPage()
    {
        return '';
    }

    public function __construct(CMS_Model_WidgetInstance $config)
    {
        $this->widgetConfig = $config;
        $this->options = $config->config;

        $class = get_class($this);
        $baseDir = dirname(Core_Autoload::getFilename($class));

        $this->view = new CMS_View(array(
            $class => $baseDir . '/templates'
        ));
    }

    public static function getPosition($template, $position)
    {
        $widgets = CMS_Model_Widget::getInstancesForTemplate($template, $position);

        $user = CMS_User::getUser();
        $view = CMS_Application::current()->View;
        $view->assign('hasPermition', WIDGET_BORDER_AROUND && $user->hasRight(null, CMS_Bazalt::ACL_GODMODE));
        $view->assign('template', $template);
        $view->assign('position', $position);

        $strWidgets = '';
        foreach ($widgets as $widgetConfig) {
            $widget = $widgetConfig->getWidgetInstance();
            if ($widget) {
                $strWidgets .= $widget->fetch();
            }
        }

        $view->assign('content', $strWidgets);
        return $view->fetch('cms/widgets/position');
    }

    public function fetch()
    {
        $template = $this->widgetConfig->widget_template;
        if (empty($template)) {
            $template = empty($this->widgetConfig->widget_template) ? 
                            $this->widgetConfig->Widget->default_template : 
                            $this->widgetConfig->widget_template;
        }
        $user = CMS_User::getUser();
        $this->view->assign('hasPermition', WIDGET_BORDER_AROUND && $user->hasRight(null, CMS_Bazalt::ACL_GODMODE));

        $this->view->assign('widget', $this);
        $this->view->assign('widgetConfig', $this->widgetConfig);
        $this->view->assign('template', $template);
        $content = $this->view->fetch($template);

        $this->view->assign('content', $content);
        return $this->view->fetch('cms/widgets/widget');
    }

    public function getTemplates()
    {
        return array();
    }
    
    public function getCustomTemplates()
    {
        $theme = CMS_Model_Theme::getByAlias(CMS_Theme::getCurrentTheme()->Alias);
        if(!$theme) {
            return array();
        }
        return CMS_Model_WidgetTemplates::getByWidgetAndTheme($this->widgetConfig->widget_id, $theme->id);
    }

    
    public static function getWidgetInstance(CMS_Model_WidgetInstance $config)
    {
        if ($config == null || !($config instanceof CMS_Model_WidgetInstance)) {
            throw new Exception('First param must be inherited from CMS_Model_WidgetInstance');
        }
        $class = $config->Widget->className;
        if ($class == 'LanguagesWidget') {
            $class = 'Widgets_Languages_Widget';
        }
        if ($class == 'NewsWidget') {
            $class = 'Widgets_News_Widget';
        }
        if (!Type::isClassExists($class)) {
            throw new Exception('Class ' . $class . ' not found');
        }
        $type = typeOf($class);
        if (!$type->isSubclassOf(__CLASS__)) {
            throw new Exception('Widget ' . $class . ' must be inherited from ' . __CLASS__);
        }
        $widget = new $class($config);
        return $widget;
    }
}
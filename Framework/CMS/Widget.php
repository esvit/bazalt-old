<?php

namespace Framework\CMS;

if (!defined('WIDGET_BORDER_AROUND')) {
    define('WIDGET_BORDER_AROUND', true);
}

abstract class Widget
{
    protected $widgetConfig;
    public $errorMessages;
    protected $options = array();
    protected $view;

    public function getConfigPage()
    {
        return '';
    }

    public function getJavascriptFile()
    {
        return null;
    }

    public function view()
    {
        return $this->view;
    }

    public function config()
    {
        return $this->widgetConfig;
    }

    public function isAvaliable()
    {
        return true;
    }

    public function __construct(Model\WidgetInstance $config)
    {
        $this->widgetConfig = $config;
        $this->options = $config->config;

        $class = get_class($this);
        $baseDir = dirname(\Framework\Core\Autoload::getFilename($class));

        $this->view = Application::current()->view()->newScope([$baseDir . '/../views']);
    }

    public static function getAllComponentWidgets()
    {
        $components = CMS\Bazalt::getComponents();

        $widgets = array();
        foreach ($components as $component) {
            $config = $component->getConfig();
            if ($config && $config['widgets']) {
                foreach ($config['widgets'] as $widget) {
                    $widgets[$widget->value] = $widget->attributes;
                    $widgets[$widget->value]['component'] = $component;
                }
            }
        }
        return $widgets;
    }

    public static function getPosition($template, $position)
    {
        $widgets = Model\Widget::getInstancesForTemplate($template, $position);

        $user = User::get();
        $view = View::root();
        $view->assign('hasPermition', WIDGET_BORDER_AROUND && $user->hasRight(null, Bazalt::ACL_CAN_ADMIN_WIDGETS));
        $view->assign('template', $template);
        $view->assign('position', $position);

        $strWidgets = '';
        foreach ($widgets as $widgetConfig) {
            try {
                $widget = $widgetConfig->getWidgetInstance();
                if ($widget) {
                    $strWidgets .= $widget->fetch();
                }
            } catch (Exception $ex) {
                if (STAGE == PRODUCTION_STAGE) {
                    $view->assign('exception', $ex);
                    ErrorCatcher::sendToErrorService($ex);
                    $content = $view->fetch('cms/widgets/exception');
                } else {
                    throw $ex; //new Exception('Exception in widget', 0, $e);
                }
            }
        }

        $view->assign('content', $strWidgets);
        return $view->fetch('cms/widgets/position');
    }

    public function getTemplate()
    {
        $template = $this->widgetConfig->widget_template;
        if (empty($template)) {
            $template = empty($this->widgetConfig->widget_template) ?
                $this->widgetConfig->Widget->default_template :
                $this->widgetConfig->widget_template;
        }
        return $template;
    }

    public function fetch()
    {
        $template = $this->getTemplate();
        $user = User::get();
        $this->view->assign('hasPermition', WIDGET_BORDER_AROUND && $user->hasRight(null, Bazalt::ACL_CAN_ADMIN_WIDGETS));

        $this->view->assign('widget', $this);
        $this->view->assign('widgetConfig', $this->widgetConfig);
        $this->view->assign('template', $template);
        try {
            $content = $this->view->fetch($template);
        } catch (Exception $e) {
            if (STAGE == PRODUCTION_STAGE) {
                $this->view->assign('exception', $e);
                CMS\ErrorCatcher::sendToErrorService($e);
                $content = $this->view->fetch('cms/widgets/exception');
            } else {
                throw $e; //new Exception('Exception in widget', 0, $e);
            }
        }

        $this->view->assign('content', $content);
        return $this->view->fetch('cms/widgets/widget');
    }

    public static function getWidgetInstance(Model\WidgetInstance $config)
    {
        if ($config == null || !($config instanceof Model\WidgetInstance)) {
            throw new \Exception('First param must be inherited from Framework\CMS\Model\WidgetInstance');
        }
        $class = $config->Widget->className;
        if (!class_exists($class)) {
            throw new \Exception('Class ' . $class . ' not found');
        }
        $type = typeOf($class);
        if (!$type->isSubclassOf(__CLASS__)) {
            throw new \Exception('Widget ' . $class . ' must be inherited from ' . __CLASS__);
        }
        $widget = new $class($config);
        return $widget;
    }
}
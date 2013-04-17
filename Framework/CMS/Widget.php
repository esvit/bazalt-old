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

        $this->view = View::root()->newScope([$baseDir . '/views']);
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
        $this->view->assign('hasPermition', WIDGET_BORDER_AROUND && $user->hasRight(null, CMS\Bazalt::ACL_CAN_ADMIN_WIDGETS));

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

        $js = '';
        $file = $this->getJavascriptFile();
        if ($file !== null) {
            $className = get_class($this);

            self::addWidgetWebservice($className);
            $js = '
                window.bz = window.bz || {}, bz.widgetConfigs = bz.widgetConfigs || {}, bz.widgetConfigs[' . $this->widgetConfig->id . '] = "' . $className . '";
                '; // додає на сторінку масив асоціацій id - name

            $jsFile =  'widget::' . $className . filemtime($file) . '::' . $file;
            if (!Assets_FileManager::exists($jsFile)) {
                if (!file_exists($file)) {
                    throw new Exception('File "' . $file . '" not found');
                }
                $jsContent = file_get_contents($file);
                $jsContent = 'bz.initWidgets = bz.initWidgets || {}, bz.initWidgets.' . $className . " = function(widget) {\n" . $jsContent . "\n}";
                Assets_FileManager::save($jsFile, $jsContent);
            }
            Assets_JS::add(Assets_FileManager::filename($jsFile));

            $js = '<script>' . $js . '</script>';
        }

        $this->view->assign('content', $content);
        return $this->view->fetch('cms/widgets/widget') . $js;
    }

    public static function addWidgetWebservice($name)
    {
        $file = CMS\Webservice::getServiceFile('widget::' . $name);

        if (!Assets_FileManager::exists($file)) {
            $comService = new eazyJsonRPC_Server($name);

            $content = $comService->__getJavascript($name);
            $content .= '
                bz.widgets = bz.widgets || {};
                bz.widgets.' . $name . ' = function(id, container) {
                    this.id = id;
                    this.container = container;
                    this.rpc = bz.webservices.' . $name . ';
                    this.rpc.options.smd.target = "' . CMS\Mapper::patternFor(CMS\Webservice::WIDGET_ROUTE_NAME) . '".replace("{widgetId}", id);
                }';
            Assets_FileManager::save($file, $content);
        }
        CMS\Webservice::addWebservice(Assets_FileManager::filename($file));
    }

    public function getTemplates()
    {
        return array();
    }

    public function getCustomTemplates()
    {
        $files = array();
        $template = $this->getTemplate();
        $folders = $this->view->getFolders();
        $engines = $this->view->getEngines();
        $baseName = $this->widgetConfig->Widget->default_template;

        foreach ($folders as $folder) {
            foreach ($engines as $ext => $engine) {
                $path = $folder . PATH_SEP . $baseName;
                foreach (glob($path . PATH_SEP . '*.' . $ext, GLOB_NOSORT) as $file) {
                    $fileName = relativePath($file, $folder . PATH_SEP);
                    $files[$fileName] = $file;
                }
            }
        }
        return $files;
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
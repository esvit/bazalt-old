<?php

namespace Framework\System\View;

class Base
{
    protected static $engines = array(
        'php' => 'Framework\System\View\PHP\Engine'
    );

    protected static $engineInstances = array();

    protected $assignedVars = array();

    protected static $assignedGlobalVars = array();

    protected $folders = array();

    public function __construct($folders = array())
    {
        $this->folders = $folders;
    }

    public function get($name)
    {
        if (isset($this->assignedVars[$name])) {
            return $this->assignedVars[$name];
        }
        if (isset(self::$assignedGlobalVars[$name])) {
            return self::$assignedGlobalVars[$name];
        }
        return null;
    }

    public static function getEngine($engine)
    {
        if (array_key_exists($engine, self::$engineInstances)) {
            return self::$engineInstances[$engine];
        }
        if (!in_array($engine, self::$engines)) {
            throw new \Exception('Unknown template engine "' . $engine . '"');
        }
        if (!class_exists($engine)) {
            throw new \Exception('Class "' . $engine . '" not found');
        }
        self::$engineInstances[$engine] = new $engine();
        return self::$engineInstances[$engine];
    }

    public static function register($ext, $class)
    {
        self::$engines[$ext] = $class;
    }

    public static function getEngines()
    {
        return self::$engines;
    }

    public static function getExtensions()
    {
        return array_keys(self::$engines);
    }

    public function assign($name, $value)
    {
        $this->assignedVars[$name] = $value;
    }

    public function assignGlobal($name, $value)
    {
        self::$assignedGlobalVars[$name] = $value;
    }

    public function assignByRef($name, &$value)
    {
        $this->assignedVars[$name] = $value;
    }

    public function addFolder($folder, $name = null)
    {
        if ($name == null) {
            $this->folders []= $folder;
        } else {
            $this->folders[$name] = $folder;
        }
    }

    public function folders($folders = null)
    {
        if ($folders !== null) {
            $this->folders = $folders;
            return $this;
        }
        return $this->folders;
    }

    public function getAssignedVars()
    {
        return array_merge(self::$assignedGlobalVars, $this->assignedVars);
    }

    protected function findTemplate($template, $ext = null)
    {
        $engines = self::$engines;
        if (!empty($ext)) {
            if (array_key_exists($ext, self::$engines)) {
                $engines = array($ext => self::$engines[$ext]);
            } else {
                $template .= '.' . $ext;
            }
        }
        $folders = array_reverse($this->folders());

        foreach ($folders as $domain => $folder) {
            foreach ($engines as $ext => $engine) {
                $file = $folder . PATH_SEP . $template . '.' . $ext;
                if (file_exists($file)) {
                    return array(
                        'engine'   => $engine,
                        'folder'   => $folder,
                        'domain'   => is_numeric($domain) ? null : $domain,
                        'file'     => $template . '.' . $ext
                    );
                }
            }
        }
        return null;
    }

    /**
     * Повертає опрацьований шаблон, якщо було передано масив шаблонів,
     * то перебирається масив і показується перший існуючий шаблон
     *
     * @param string|array  $template Назва шаблону або масив шаблонів
     * @param null|array    $vars
     * @throws \Exception Якщо шаблон не знайдено
     * @return string
     */
    public function fetch($template, $vars = null)
    {
        $viewTemplate = null;
        if (is_array($template)) {
            foreach ($template as $item) {
                $ext = pathinfo($item, PATHINFO_EXTENSION);
                if (!empty($ext)) {
                    $item = substr($item, 0, -(strlen($ext) + 1));
                }
                $file = $this->findTemplate($item, $ext);
                if (!empty($file)) {
                    $viewTemplate = $file;
                    break;
                }
            }
        } else {
            $viewTemplate = $template;
            $ext = pathinfo($viewTemplate, PATHINFO_EXTENSION);
            if (!empty($ext)) {
                $viewTemplate = substr($viewTemplate, 0, -(strlen($ext) + 1));
            }

            $viewTemplate = $this->findTemplate($viewTemplate, $ext);
        }
        if (empty($viewTemplate)) {
            throw new \Exception('Cann\'t find template "' . print_r($template, true) . '". ' . print_r($this->folders(), true));
        }
        if ($vars != null) {
            $oldVars = $this->assignedVars;
            $this->assignedVars = $vars;
        }
        $this->assignedVars['_view'] = $this;

        \Framework\Core\Logger::getInstance()->info('Show template "' . $viewTemplate['file'] . '" from folder: "' . $viewTemplate['folder'] . '"');

        $engine = self::getEngine($viewTemplate['engine']);
        $engine->setLocaleDomain($viewTemplate['domain']);
        $content = $engine->fetch($viewTemplate['folder'], $viewTemplate['file'], $this);

        if ($vars != null) {
            $this->assignedVars = $oldVars;
        }
        return $content;
    }

    public function display($template, $vars = null)
    {
        echo $this->fetch($template, $vars);
    }
}
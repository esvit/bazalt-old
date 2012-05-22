<?php

class View_Base extends Object implements ISingleton
{
    protected static $engines = array();

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
            throw new Exception('Unknown template engine "' . $engine . '"');
        }
        if (!class_exists($engine)) {
            throw new Exception('Class "' . $engine . '" not found');
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

    public function getFolders()
    {
        return $this->folders;
    }

    public function setFolders($folders)
    {
        $this->folders = $folders;
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
        $folders = array_reverse($this->getFolders());

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

    public function fetch($template, $vars = null)
    {
        $origTemplate = $template;
        $ext = pathinfo($template, PATHINFO_EXTENSION);
        if (!empty($ext)) {
            $template = substr($template, 0, -(strlen($ext) + 1));
        }

        $template = $this->findTemplate($template, $ext);
        if (empty($template)) {
            throw new Exception('Cann\'t find template "' . $origTemplate . '". ' . print_r($this->getFolders(), true));
        }
        if ($vars != null) {
            $oldVars = $this->assignedVars;
            $this->assignedVars = $vars;
        }
        $this->assignedVars['_view'] = $this;

        $this->getLogger()->info('Show template "' . $template['file'] . '" from folder: "' . $template['folder'] . '"');
        $engine = self::getEngine($template['engine']);
        $engine->setLocaleDomain($template['domain']);
        $content = $engine->fetch($template['folder'], $template['file'], $this);

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

View_Base::register('php', 'View_PhpEngine');
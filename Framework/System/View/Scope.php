<?php

namespace Framework\System\View;

class Scope implements \ArrayAccess
{
    /**
     * @var array Список підтримуємих шаблонізаторів
     */
    protected static $engines = [];

    protected static $rootScope = null;

    /**
     * @var Scope
     */
    protected $parentScope = null;

    protected $childScopes = [];

    protected $assignedVars = [];

    protected static $assignedGlobalVars = [];

    protected $folders = [];

    protected function __construct($folders = [])
    {
        $this->folders = $folders;
    }

    /**
     * Return root scope
     *
     * @return Scope
     */
    public static function root()
    {
        if (!self::$rootScope) {
            $class = get_called_class();
            self::$rootScope = new $class();
        }
        return self::$rootScope;
    }

    /**
     * Create new scope
     *
     * @param array $folders
     * @return Scope
     */
    public function newScope($folders = [])
    {
        $scope = new Scope($folders);
        $scope->parentScope = $this;
        $this->childScopes []= $scope;
        return $scope;
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

    public static function engine($extension, Engine $engine = null)
    {
        if ($engine != null) {
            /* як виявилось потрібна змога заміняти шаблонізатори
             if (array_key_exists($extension, self::$engines)) {
                throw new \Exception('Engine for "' . $extension . '" already exists');
            }*/
            self::$engines[$extension] = $engine;
            return $engine;
        }
        if (array_key_exists($extension, self::$engines)) {
            return self::$engines[$extension];
        }
        if (!in_array($extension, self::$engines)) {
            throw new \Exception('Unknown template engine "' . $extension . '"');
        }
        if (!class_exists($engine)) {
            throw new \Exception('Class "' . $engine . '" not found');
        }
        self::$engines[$extension] = new $engine();
        return self::$engines[$extension];
    }

    public static function engines()
    {
        return self::$engines;
    }

    public function assign($name, $value)
    {
        $this->assignedVars[$name] = $value;
    }

    public static function assignGlobal($name, $value)
    {
        self::$assignedGlobalVars[$name] = $value;
    }

    public function assignByRef($name, &$value)
    {
        $this->assignedVars[$name] = $value;
    }

    public function folders($folders = null)
    {
        if ($folders !== null) {
            $this->folders = $folders;
            return $this;
        }
        return $this->folders;
    }

    public function variables()
    {
        $vars = ($this->parentScope) ? $this->parentScope->variables() : [];
        return array_merge($vars, self::$assignedGlobalVars, $this->assignedVars);
    }

    protected function findTemplate($template, $ext = null, &$folders = [])
    {
        $engines = self::$engines;
        if (!empty($ext)) {
            if (array_key_exists($ext, self::$engines)) {
                $engines = array($ext => self::$engines[$ext]);
            } else {
                $template .= '.' . $ext;
            }
        }
        $folders = $this->folders();
        if ($this->parentScope) {
            $folders = array_merge($this->parentScope->folders(), $folders);
        }
        $folders = array_reverse($folders);

        foreach ($folders as $folder) {
            foreach ($engines as $ext => $engine) {
                $file = $folder . PATH_SEP . $template . '.' . $ext;
                if (file_exists($file)) {
                    return array(
                        'engine'   => $engine,
                        'folder'   => $folder,
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

            $viewTemplate = $this->findTemplate($viewTemplate, $ext, $folders);
        }
        if (empty($viewTemplate)) {
            throw new \Exception('Cann\'t find template "' . print_r($template, true) . '". ' . print_r($folders, true));
        }
        $oldVars = [];
        if ($vars != null) {
            $oldVars = $this->assignedVars;
            $this->assignedVars = $vars;
        }
        $this->assignedVars['_view'] = $this;

        \Framework\Core\Logger::getInstance()->info('Show template "' . $viewTemplate['file'] . '" from folder: "' . $viewTemplate['folder'] . '"');

        $engine = $viewTemplate['engine'];
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

    public function __set($name, $value)
    {
        $this->assignedVars[$name] = $value;
    }

    public function __get($name)
    {
        return $this->assignedVars[$name];
    }

    public function offsetExists($offset)
    {
        return isset($this->assignedVars[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->assignedVars[$offset]) ? $this->assignedVars[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {
        $this->{$offset} = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->assignedVars[$offset]);
    }
}

Scope::engine('html', new Engine());
Scope::engine('php', new PHP\Engine());
<?php

class Routing_Rule extends Object
{
    const REGEX_SYMBOL = '#';

    protected static $allowConditionTypes = array(
        'function', 'method'
    );

    /**
     * Route rule
     */
    protected $rule;

    /**
     * Parts of route rule
     */
    protected $parts = array();

    /**
     * Names of route variables
     */
    protected $vars = array();

    /**
     * Route name
     */
    protected $name;

    protected $defaults;

    protected $conditions;

    protected $mapper;

    protected $baseUrl;

    protected $urlPattern = '';

    /**
     * Функції, що будуть виконуватись перед екшеном контролеру
     */
    protected $preActions = array();

    public function __construct(Mapper $mapper, $rule, $baseUrl = '/', $defaults = array())
    {
        $this->mapper = $mapper;
        $this->defaults = $defaults;
        $this->baseUrl = trim($baseUrl, '/');
        $this->rule = trim($rule, '/');

        $this->setPattern($this->rule);
    }

    public function addPreAction($callback)
    {
        if (!is_callable($callback)) {
            throw new Exception('Invalid preaction callback');
        }
        $this->preActions []= $callback;
    }

    /**
     * Set route name
     *
     * @param string $name name of the route
     * @return string Name of the route
     */
    public function name($name = null)
    {
        if ($name != null) {
            $this->name = $name;
            Mapper::addNamedRoute($name, $this);
            return $this;
        }
        return $this->name;
    }

    public function toUrl($params = array())
    {
        $parts = array();
        foreach ($this->parts as $part) {
            $urlPart = '';
            switch ($part->type()) {
                case Routing_Part::URL_PART:
                case Routing_Part::STATIC_PART:
                    $parts []= $part->value();
                    break;
                case Routing_Part::DYNAMIC_PART:
                    $parts []= $part->getUrlPart($params);
                    break;
            }
        }
        return '/' . implode('/', $parts);
    }

    public function toUrlPattern()
    {
        $parts = array();
        foreach ($this->parts as $part) {
            $urlPart = '';
            switch ($part->type()) {
                case Routing_Part::URL_PART:
                case Routing_Part::STATIC_PART:
                    $parts []= $part->value();
                    break;
                case Routing_Part::DYNAMIC_PART:
                    $parts []= $part->getUrlPattern();
                    break;
            }
        }
        return '/' . implode('/', $parts);
    }

    public function action($action)
    {
        $this->defaults['action'] = $action;
        return $this;
    }

    public function controller($controller)
    {
        $this->defaults['controller'] = $controller;
        return $this;
    }

    public function removeDefaultParam($name)
    {
        if (isset($this->defaults[$name])) {
            unset($this->defaults[$name]);
        }
        return $this;
    }

    public function condition($condType, $options = array())
    {
        if (!in_array($condType, self::$allowConditionTypes)) {
            throw new Exception('Unknown condition type "' . $condType . '"');
        }
        $this->conditions []= array('type' => $condType, 'options' => $options);
        return $this;
    }

    public function requirement($name, $reqirePattern = null)
    {
        if (!array_key_exists($name, $this->vars)) {
            throw new Exception('Parameter "' . $name . '" not found in route "' .  $this->rule . '"');
        }
        $part = $this->parts[$this->vars[$name]];
        if ($reqirePattern == null) {
            return $part->requirement($name);
        }
        $part->requirement($name, $reqirePattern);
        $this->parts[$this->vars[$name]] = $part;
        return $this;
    }

    public function setPattern($rule)
    {
        $num = 0;

        $parts = explode('/', $this->rule);
        $this->parts = array();
        $this->vars = array();
        if (!empty($this->baseUrl)) {
            $rPart = new Routing_Part($this->baseUrl);

            $this->parts[$num++] = $rPart;

            $partVars = $rPart->getVariables();
            foreach ($partVars as $var) {
                $this->vars[$var] = $num - 1;
            }

            if (empty($this->rule)) { // if route '/' and baseUrl not empty ignore url parts
                $parts = array();
            }
        }

        foreach ($parts as $part) {
            $rPart = new Routing_Part($part);
            $this->parts[$num++] = $rPart;
            $partVars = $rPart->getVariables();

            # check for unique vars
            $arr = array_intersect(array_keys($this->vars), $partVars);
            if (count($arr) > 0) {
                throw new Exception('Parameter "' . current($arr) . '" can\'t present in route twice ("' . $rule . '")');
            }

            foreach ($partVars as $var) {
                $this->vars[$var] = $num - 1;
            }
        }
    }


    public function getPattern()
    {
        $pattern = self::REGEX_SYMBOL . '^';
        foreach ($this->parts as $part) {
            $pattern .= preg_quote('/', self::REGEX_SYMBOL);
            $pattern .= $part->getPattern();
        }
        $pattern .= '$' . self::REGEX_SYMBOL . 'i';
        return $pattern;
    }

    protected function checkConditions($url, &$params)
    {
        if (!is_array($this->conditions)) {
            return true;
        }
        foreach ($this->conditions as $condition) {
            $options = $condition['options'];
            switch ($condition['type']) {
                case 'method':
                    if (!$this->checkMethodCondition($url, $params, $this, $options)) {
                        return false;
                    }
                    break;
                case 'function':
                    if (!$this->checkFunctionCondition($url, $params, $this, $options)) {
                        return false;
                    }
                    break;
            }
        }
        return true;
    }

    protected function checkMethodCondition($url, &$params, $route, $options)
    {
        if (!is_array($options)) {
            $options = array($options);
        }
        return in_array($_SERVER['REQUEST_METHOD'], $options);
    }

    protected function checkFunctionCondition($url, &$params, $route, $options)
    {
        $callback = $options['callback'];
        unset($options['callback']);
        if (!is_callable($callback)) {
            throw new Exception('Invalid callback ' . print_r($options));
        }
        $callParams = array($url, $route, &$params, $options);
        return call_user_func_array($callback, $callParams);
    }

    protected function createRoute($url, $params)
    {
        return new Routing_Route($url, $this, $params);
    }

    public function compare($url)
    {
        $profile = Logger::start(__CLASS__, __FUNCTION__);
        $params = array();
        $url = rtrim($url, '/');
        if (empty($url)) {
            $url = '/';
        }

        $pattern = $this->getPattern();
        if (preg_match($pattern, $url, $params)) {
            foreach ($params as $key => $param) {
                if (!array_key_exists(strval($key), $this->vars)) {
                    unset($params[$key]);
                } else {
                    $params[$key] = urldecode($param);
                }
            }
            $params = array_merge($this->defaults, $params);
            if ($this->checkConditions($url, $params)) {
                $this->getLogger()->info('Url "' . $url . '" matched "' . htmlentities($pattern, ENT_COMPAT, 'UTF-8') . '" with params: ' . print_r($params, true));
                Logger::stop($profile);
                return $this->createRoute($url, $params);
            }
        }
        Logger::stop($profile);
        $this->getLogger()->info('Url "' . $url . '" not matched "' . htmlentities($pattern, ENT_COMPAT, 'UTF-8') . '"');
        return false;
    }

    public function submapper($subDir, $defaults = array())
    {
        if (is_array($subDir)) {
            $defaults = $subDir;
            $subDir = '';
        } else if (empty($subDir)) {
            throw new Exception('Invaid subpath for submapper');
        }
        $class = get_class($this->mapper);
        $options = $this->mapper->Options;
        $options['defaults'] = array_merge($this->mapper->Defaults, $defaults);
        $map = new $class($this->mapper->BaseUrl . $subDir, $options);

        $this->mapper->connectSubmapper($map, $subDir, array_merge($this->defaults, $defaults));
        return $map;
    }
}
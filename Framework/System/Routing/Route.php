<?php

namespace Framework\System\Routing;

// add trailed slash in url
// example:  http://../test => http://.../test/
if (!defined('SEO_LAST_SLASH_IN_URL')) {
    define('SEO_LAST_SLASH_IN_URL', true);
}

// Allow add script name to url like: /index.php/test/
// if set true, urlFor generate: /test/
if (!defined('ROUTING_NO_SCRIPT_NAME')) {
    define('ROUTING_NO_SCRIPT_NAME', false);
}

use Framework\Core\Helper\Url;

class Route
{
    const REGEX_SYMBOL = '#';

    /**
     * Формат імені функції-екшена
     */
    const ACTION_NAME_FORMAT = '%sAction';

    const DEFAULT_ACTION_NAME = 'default';

    protected static $root = null;

    protected static $urlPrefix = '';

    /**
     * @var Route[]
     */
    protected static $names = array();

    protected $name = null;

    protected $params = [];

    protected $conditions = [];

    protected $baseRule = null;

    protected $baseRoute = null;

    protected $compiled = null;

    /**
     * @return Route
     */
    public static function root()
    {
        if (self::$root == null) {
            self::$root = new Route('home', '/', null);
        }
        return self::$root;
    }

    public static function clear()
    {
        self::$names = [];
        self::$root = null;
    }

    public static function get($name)
    {
        if (!array_key_exists($name, self::$names)) {
            throw new \InvalidArgumentException('Route with name "' . $name . '" not found');
        }
        return self::$names[$name];
    }

    protected function __construct($name, $baseRule, Route $baseRoute = null)
    {
        if (!preg_match("/^[A-Za-z][A-Za-z_0-9\.]*[A-Za-z0-9]$/", $name)) {
            throw new \InvalidArgumentException('Route name must begin and end from symbol and contains only latin symbols and numbers');
        }
        if (array_key_exists($name, self::$names)) {
            throw new \InvalidArgumentException('Route with name "' . $name . '" already exists');
        }

        $this->name = $name;

        $this->baseRule = trim($baseRule, '/');
        if ($baseRoute && empty($this->baseRule)) {
            throw new \InvalidArgumentException('Route rule("' . $baseRule . '") cannot be empty');
        }
        if ($baseRoute) {
            $this->baseRule = $baseRoute->baseRule . '/' . $this->baseRule;
        }

        $this->baseRoute = $baseRoute;

        self::$names[$name] = $this;
    }

    public function name()
    {
        return $this->name;
    }

    public function params()
    {
        return $this->params;
    }

    public function param($name, $value = null)
    {
        if ($value === null) {
            return $this->params[$name];
        }
        $this->params[$name] = $value;
        return $this;
    }

    public function compareTo($url, &$params = array())
    {
        if ($this->compiled == null) {
            $this->compiled = self::compileRule($this->baseRule);
        }
        $url = rtrim($url, '/');
        if (preg_match($this->compiled, $url, $params)) {
            $params = array_map('urldecode', $params);
            foreach ($this->conditions as $name => $conditions) {
                foreach ($conditions as $condition) {
                    if ((is_string($condition) && !preg_match(self::REGEX_SYMBOL . '^' . $condition . '$' . self::REGEX_SYMBOL, $params[$name])) ||
                        (is_callable($condition) && !$condition($url, $name, $params[$name], $params))) {
                        return false;
                    }
                }
            }
            unset($params[0]); // remove url form matches
            return true;
        }
        return false;
    }

    public static function compileRule($rule)
    {
        $patterns = [
            '/{([^{\/]+):(.*)}/', // /{test:\d+}/
            '/{([^{\/]+)}/',      // /{test}/
            '/\[([^\/\[]+)\]/'    // /[test]/
        ];
        $replace = [
            '(?P<$1>$2)',
            '(?P<$1>[^/]+)',
            '(?P<$1>.+)?'
        ];
        $rule = self::REGEX_SYMBOL . '^' . preg_replace($patterns, $replace, $rule) . '$' . self::REGEX_SYMBOL;
        return $rule;
    }

    /**
     * @param $url
     * @return Route|null
     */
    public static function find($url)
    {
        $params = [];
        foreach (self::$names as $name => $route) {
            if ($route->compareTo($url, $params)) {
                $newRoute = clone $route;
                $newRoute->params = array_merge($newRoute->params, $params);
                return $newRoute;
            }
        }
        return null;
    }

    public function connect($name, $rule, $options = array())
    {
        $route = new Route($name, $rule, $this);
        $route->params = $options;

        return $route;
    }

    public function where($name, $regExpOrFunction)
    {
        if (!is_string($regExpOrFunction) && !is_callable($regExpOrFunction)) {
            throw new \InvalidArgumentException('Second argument must be function or regular expression');
        }
        if (!isset($this->conditions[$name])) {
            $this->conditions[$name] = [];
        }
        $this->conditions[$name] []= $regExpOrFunction;
        return $this;
    }

    public static function urlFor($name, $params = array(), $withHostname = false)
    {
        $query = array();
        $prms = array();
        $pattern = self::patternFor($name, $withHostname);
        foreach ($params as $key => $param) {
            if (substr($key, 0, 1) == '?') {
                $query[substr($key, 1)] = $param;
            } else {
                if (is_object($param) && $param instanceof Sluggable) {
                    $param = $param->toUrl(self::$names[$name]); // Перевірка на існування роута в patternFor
                    $prms['[' . $key . ']'] = $param;
                } else if (is_object($param)) {
                    throw new \InvalidArgumentException('Value of parameter "' . $key . '" ' .
                                                        'must be string or implements Sluggable interface.' .
                                                        'Given "' . get_class($param) . '"');
                }
                $prms['{' . $key . '}'] = $param;
            }
        }
        $url = strtr($pattern, $prms);
        if (count($query) > 0) {
            $url .= '?' . http_build_query($query);
        }
        return $url;
    }

    public static function patternFor($name, $withHostname = false)
    {
        static $patterns;

        $cacheKey = false;//$this->getCacheKey('generateUrlPattern' . $name . $withHostname);
        $url = false;//Cache::Singleton()->getCache($cacheKey);

        if (!$url) {
            if (!array_key_exists($name, self::$names)) {
                throw new \Exception('Route with name "' . $name . '" not found');
            }
            if (!is_array($patterns)) {
                $patterns = array();
            }

            $url = isset($patterns[$name]) ? $patterns[$name] : self::$names[$name]->baseRule;
            // remove require params from url
            while (($pos = strpos($url, ':')) !== false) {
                if (($endPos = strpos($url, '}', $pos)) !== false || ($endPos = strpos($url, ']', $pos)) !== false) {
                    $url = substr($url, 0, $pos) . substr($url, $endPos);
                }
            }
            $patterns[$name] = $url;

            //Додає в кінень посилання шлеш, для СЕО
            if (SEO_LAST_SLASH_IN_URL && substr($url, -1) != '/') {
                if (substr($url, -4, 1) != '.') { // якщо не файл
                    $url .= '/';
                }
            }
            //Cache::Singleton()->setCache($cacheKey, $url);
        }

        $prefix = self::$urlPrefix;

        // for url like /index_dev.php/etc/...
        if (array_key_exists('PATH_INFO', $_SERVER) && !ROUTING_NO_SCRIPT_NAME) {
            $prefix = $_SERVER['PATH_INFO'] . $prefix;
        }
        $prefix = rtrim($prefix, '/');
        $url = $prefix . $url;
        if ($withHostname) {
            $url = Url::getHostname() . $url;
        }
        return $url;
    }

    public function dispatch()
    {
        $params = $this->params();

        if (empty($params['controller'])) {
            throw new \Exception('Controller is not set');
        }
        if (empty($params['action'])) {
            $params['action'] = self::DEFAULT_ACTION_NAME;
        }
        $controllerClass = $params['controller'];
        $action = sprintf(self::ACTION_NAME_FORMAT, strToLower($params['action']));

        unset($params['controller']);
        unset($params['action']);

        $controllerRef = new \ReflectionClass($controllerClass);
        $controller = $controllerRef->newInstanceArgs();

        if (!$controllerRef->hasMethod($action)) {
            throw new \Exception('Action "' . $action . '" not found in class "' . $controllerClass . '"');
        }

        $actionRef = $controllerRef->getMethod($action);
        $args = $actionRef->getParameters();
        foreach ($args as $k => $param) {
            unset($args[$k]);
            $args[$param->name] = null;
        }
        $controller->preAction($action, $params);
        foreach ($params as $name => $param) {
            if (is_numeric($name)) {
                continue;
            }
            if (!array_key_exists($name, $args)) {
                throw new \Exception('Method "' . $controllerClass . '::' . $action . '" must have param' .
                    ' "' . $name . '" as in route "' . $this->name() . '"' .
                    'or set their default value');
            }
            $args[$name] = $param;
        }
        /*foreach ($this->rule->PreActions as $callback) {
            call_user_func($callback, $this);
        }*/
        $actionRef->invokeArgs($controller, $args);
        //call_user_func_array(array($controller, $action), $fargs);
    }
}

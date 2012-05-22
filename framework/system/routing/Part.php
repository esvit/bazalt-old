<?php

class Routing_Part
{
    const STATIC_PART = 'static';

    const DYNAMIC_PART = 'dynamic';

    const URL_PART = 'url';

    const DYNAMIC_URL_PART = 'dynamic_url';

    const LAST_STATIC_PART = 9999;

    const REGEX_SYMBOL = '#';

    protected $value;

    /**
     * Variables
     */
    protected $vars = array();

    /**
     * Optional variables
     */
    protected $optionalVars = array();

    /**
     * Default values
     */
    protected $defaults = array();

    protected $type = self::STATIC_PART;

    protected $statics = array();

    /**
     * Requirements
     */
    protected $requirements = array();

    public function requirement($name, $reqirePattern = null)
    {
        if (!in_array($name, $this->vars)) {
            throw new Exception('Parameter "' . $name . '" not found in route part "' .  $this->value . '"');
        }
        if ($reqirePattern == null) {
            return $this->requirements[$name];
        }
        $this->requirements[$name] = $reqirePattern;
        return $this;
    }

    public function type($type = null)
    {
        if ($type != null) {
            $this->type = $type;
            return $this;
        }
        return $this->type;
    }

    public function value()
    {
        return $this->value;
    }

    public function __construct($part)
    {
        $this->value = $part;
        $this->type = $this->getPartType($part);
    }

    public function getVariables()
    {
        return $this->vars;
    }

    protected function parseVar($var)
    {
        $isDinamic = ($var{0} == '[');

        $var = substr($var, 1, -1);
        $varParts = explode(':', $var);
        if (count($varParts) > 1) {
            $var = $varParts[0];
            $this->requirements[$var] = $varParts[1];
        }
        $var = preg_replace('[\W]', '', $var);
        if ($isDinamic) {
            $this->optionalVars[$var] = true;
        }
        return $var;
    }

    public function compare($part)
    {
        $part = urldecode($part);
        switch ($this->type) {
            case self::STATIC_PART:
            case self::URL_PART:
                return ($part == $this->value);
            case self::DYNAMIC_PART:
                $pattern = $this->getPattern();
                if (preg_match($pattern, $part, $params)) {
                    $params = array_map('urldecode', $params);
                    return $params;
                }
                break;
        }
        return false;
    }

    public function getUrlPart($params = array())
    {
        $url = '';

        foreach ($this->vars as $i => $key) {
            # if have static prefix
            if (!empty($this->statics[$i])) {
                $url .= $this->statics[$i];
            }

            if (!array_key_exists($key, $params)) {
                throw new Exception('Parameter "' . $key . '" not found in param list');
            }
            $value = $params[$key];

            if (array_key_exists($key, $this->requirements)) {
                $requirement = self::REGEX_SYMBOL . '^' . $this->requirements[$key] . '$' . self::REGEX_SYMBOL;
                if (!preg_match($requirement, $value)) {
                    throw new Exception('Invalide parameter "' . $key . '" value "' . $value . '". Pattern "' . $this->requirements[$key] . '"');
                }
            }
            $url .= $value;
        }
        if (!empty($this->statics[self::LAST_STATIC_PART])) {
            $url .= $this->statics[self::LAST_STATIC_PART];
        }
        return $url;
    }

    public function getUrlPattern()
    {
        $url = '';

        foreach ($this->vars as $i => $key) {
            # if have static prefix
            if (!empty($this->statics[$i])) {
                $url .= $this->statics[$i];
            }

            $url .= '{' . $key . '}';
        }
        if (!empty($this->statics[self::LAST_STATIC_PART])) {
            $url .= $this->statics[self::LAST_STATIC_PART];
        }
        return $url;
    }

    public function getPattern()
    {
        if ($this->type == self::STATIC_PART) {
            return preg_quote($this->value, self::REGEX_SYMBOL);
        } else if ($this->type == self::URL_PART) {
            return preg_quote(trim($this->value, '/'), self::REGEX_SYMBOL);
        }
        $regex = '';

        foreach ($this->vars as $i => $key) {
            $requirement = '[^/]+';
            # if have static prefix
            if (!empty($this->statics[$i])) {
                $regex .= preg_quote($this->statics[$i], self::REGEX_SYMBOL);
            }

            if (array_key_exists($key, $this->requirements)) {
                $requirement = $this->requirements[$key];
            }
            if (array_key_exists($key, $this->optionalVars)) {
                $regex .= '(?P<' . preg_quote($key, self::REGEX_SYMBOL) . '>.+)?';
            } else {
                $regex .= '(?P<' . preg_quote($key, self::REGEX_SYMBOL) . '>' . $requirement . ')';
            }
        }
        if (!empty($this->statics[self::LAST_STATIC_PART])) {
            $regex .= preg_quote($this->statics[self::LAST_STATIC_PART], self::REGEX_SYMBOL);
        }
        return $regex;
    }

    protected function getPartType($part)
    {
        $a = "(\[.+\])";
        $b = "(\{.+\})";
        $pattern = self::REGEX_SYMBOL . '(.*)(' . $a . '|' . $b . ')' . self::REGEX_SYMBOL . 'Ui';

        if (preg_match_all($pattern, $part, $matches, PREG_SET_ORDER)) {
            # last static symbols
            preg_match(self::REGEX_SYMBOL . '[^\]\}]*$' . self::REGEX_SYMBOL, $part, $lastStatic);
            if (!empty($lastStatic[0])) {
                $this->statics[self::LAST_STATIC_PART] = /*preg_quote(*/$lastStatic[0]/*, self::REGEX_SYMBOL)*/;
            }
            foreach ($matches as $i => $match) {
                $this->vars[] = $this->parseVar($match[2]);
                if (!empty($match[1])) {
                    $this->statics[$i] = /*preg_quote(*/$match[1]/*, self::REGEX_SYMBOL)*/;
                }
            }
            ksort($this->statics);
            return self::DYNAMIC_PART;
        } else {
            return self::STATIC_PART;
        }
    }
}
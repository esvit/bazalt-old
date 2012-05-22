<?php

require_once dirname(__FILE__) . '/Neon.php';

class Neon_Adapter
{
    /** @internal */
    const INHERITING_SEPARATOR = '<', // child < parent
          PREVENT_MERGING = '!';

    const EXTENDS_KEY = '_extends',
          OVERWRITE = true;

    const COMMON_SECTION = 'common';

    protected $configuration = array();

    /**
     * Reads configuration from NEON file.
     * @param  string  file name
     * @return array
     */
    public function load($fileName)
    {
        if (!is_file($fileName)) {
            throw new InvalidArgumentException(sprintf('File %s not found', $fileName));
        }
        $content = file_get_contents($fileName);
        $configuration = $this->process((array) Neon::decode($content));

        $this->configuration = isset($configuration[STAGE]) ? $configuration[STAGE] : $configuration[self::COMMON_SECTION];
        if (isset($this->configuration[self::EXTENDS_KEY]) && isset($configuration[$this->configuration[self::EXTENDS_KEY]])) {
            $this->configuration = array_merge($configuration[$this->configuration[self::EXTENDS_KEY]], $this->configuration);
        }
        return $this->configuration;
    }

    public function get($section)
    {
        $value = null;
        $sections = explode('/', $section);
        while (count($sections) > 0) {
            $config = is_null($value) ? $this->configuration : $value;
            $section = array_shift($sections);

            if (!isset($config[$section])) {
                return null;
            }
            $value = $config[$section];
        }
        return $value;
    }

    private function process(array $arr)
    {
        $res = array();
        foreach ($arr as $key => $val) {
            if (substr($key, -1) === self::PREVENT_MERGING) {
                if (!is_array($val) && $val !== NULL) {
                    throw new Neon_Exception_InvalidState("Replacing operator is available only for arrays, item '$key' is not array.");
                }
                $key = substr($key, 0, -1);
                $val[self::EXTENDS_KEY] = self::OVERWRITE;

            } elseif (preg_match('#^(\S+)\s+' . self::INHERITING_SEPARATOR . '\s+(\S+)$#', $key, $matches)) {
                if (!is_array($val) && $val !== NULL) {
                    throw new Neon_Exception_InvalidState("Inheritance operator is available only for arrays, item '$key' is not array.");
                }
                list(, $key, $val[self::EXTENDS_KEY]) = $matches;
                if (isset($res[$key])) {
                    throw new Neon_Exception_InvalidState("Duplicated key '$key'.");
                }
            }

            if (is_array($val)) {
                $val = $this->process($val);
            } elseif ($val instanceof Neon_Entity) {
                $val = (object) array('value' => $val->value, 'attributes' => $this->process($val->attributes));
            }
            $res[$key] = $val;
        }
        return $res;
    }
}
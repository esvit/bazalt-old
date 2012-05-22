<?php

using('Framework.Vendors.Neon');

class Assets_Package implements Config_IConfigurable
{
    protected static $instance = null;

    protected $globalFile = null;

    protected $packagesDir = null;

    protected $file = null;

    protected $data = null;

    protected static $packages = null;

    const PACKAGE_FILE = 'package.cfg';

    public function __construct($file = null)
    {
        if ($file) {
            if (!file_exists($file)) {
                throw new Exception('File "' . $file . '" not found');
            }
            $content = file_get_contents($file);
            $this->data = Neon::decode($content);

            $this->file = $file;
        }
    }

    public static function &getInstance()
    {
        if (self::$instance == null) {
            $className = __CLASS__;
            self::$instance = new $className;
            Configuration::init('assets/packages', self::$instance);
        }
        return self::$instance;
    }

    public function configure($config)
    {
        $this->globalFile = isset($config['global']) ? Configuration::replaceConstants($config['global']) : null;

        $this->packagesDir = isset($config['packagesDir']) ? Configuration::replaceConstants($config['packagesDir']) : null;
    }

    public function connect()
    {
        if (!$this->data) {
            return;
        }

        $baseDir = dirname($this->file);
        if (isset($this->data['head'])) {
            foreach ($this->data['head'] as $head) {
                $head = str_replace('%package_dir%', relativePath($baseDir), $head);
                CMS_View::$headAppendString .= $head;
            }
        }

        if (isset($this->data['dependency'])) {
            foreach ($this->data['dependency'] as $package) {
                Assets_JS::addPackage($package);
            }
        }

        if (isset($this->data['js'])) {
            foreach ($this->data['js'] as $file) {
                Assets_JS::add($baseDir . PATH_SEP . $file);
            }
        }

        if (isset($this->data['css'])) {
            foreach ($this->data['css'] as $file) {
                Assets_CSS::add($baseDir . PATH_SEP . $file);
            }
        }
    }

    /**
     * Parse file with list of packages
     *
     * @return array
     */
    public function parsePackagesFile()
    {
        if ($this->globalFile == null || !file_exists($this->globalFile)) {
            return false;
        }
        $content = file_get_contents($this->globalFile);
        self::$packages = Neon::decode($content);
        return true;
    }

    public function getPackage($name, $version = null)
    {
        if (self::$packages == null) {
            self::parsePackagesFile();
        }
        if (!isset(self::$packages[$name])) {
            throw new Exception('Package with name "' . $name . '" not found (' . $this->globalFile . ')');
        }
        $folder = self::$packages[$name];

        // antihacker
        if (substr_count($folder, '..') != false) {
            throw new Exception('Package folder "' . $folder . '" cannot contains ".."');
        }

        $moduleFile = $this->packagesDir . '/' . $folder . '/' . self::PACKAGE_FILE;
        $module = new Assets_Package($moduleFile);

        return $module;
    }
}
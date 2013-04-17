<?php

namespace Framework\System\Assets;

class FileManager
{
    protected static $instance = null;

    /**
     * Специфічний домен для файлів (домен для віддачі статики)
     */
    protected $domain = null;

    protected $baseDir = null;

    protected $originalDir = null;

    protected static $folder = null;

    /**
     * Файли
     */
    protected $files = array(
        'css' => array(),
        'js'  => array()
    );

    protected $filters = array(
        'css' => array(),
        'js'  => array()
    );

    protected $modificators = array();

    /**
     * @return Assets_FileManager
     */
    public static function &getInstance()
    {
        if (self::$instance == null) {
            $className = __CLASS__;
            self::$instance = new $className;
            Config_Loader::init('assets', self::$instance);
        }
        return self::$instance;
    }

    public function configure($config)
    {
        $this->baseDir = isset($config['baseDir']) ? Config_Loader::replaceConstants($config['baseDir']) : null;

        $this->originalDir = isset($config['originalDir']) ? Config_Loader::replaceConstants($config['originalDir']) : null;

        $this->domain = isset($config['domain']) ? Config_Loader::replaceConstants($config['domain']) : null;

        if (isset($config['js'])) {
            if (isset($config['js']['filters'])) {
                foreach ($config['js']['filters'] as $filter) {
                    $className = $filter->value;
                    $this->filters['js'] []= new $className($filter->attributes);
                }
            }
        }
        if (isset($config['css'])) {
            if (isset($config['css']['filters'])) {
                foreach ($config['css']['filters'] as $filter) {
                    $className = $filter->value;
                    $this->filters['css'] []= new $className($filter->attributes);
                }
            }
        }
        /*$modificators = $node->node('modificators');
        if ($modificators) {
            foreach ($modificators->nodes() as $modificator) {
                $class = $modificator->name();
                if (!class_exists($class)) {
                    throw new Exception('Unknown modificator class "' . $class . '"');
                }
                $mod = new $class();
                $mod->attach($this);
                $mod->loadWebConfig($modificator);
                $this->modificators []= $mod;
            }
        }*/
    }

    private static function isRelativePath($path, $relPath = PUBLIC_DIR)
    {
        $relPath = str_replace('\\', '/', $relPath);
        $path = str_replace('\\', '/', $path);

        return (strpos($path, $relPath) === 0);
    }

    public function getRelativePath($file)
    {
        if (self::isRelativePath($file)) {
            $file = relativePath($file);
        } else {
            $dir = $this->originalDir . relativePath(dirname($file), SITE_DIR);
            if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
                throw new Exception('Cant create folder "' . $dir . '"');
            }
            $fileName = $dir . '/' . basename($file);
            if (!is_file($fileName) && !is_link($fileName)) {
                symlink($file, $fileName);
            }

            $file = relativePath($fileName);
        }
        return $file;
    }

    /**
     * Add style file
     *
     * @param string $file      Filename
     * @param string $type      File type
     * @param string $condition Condition (optional)
     * @return void
     */
    public function add($file, $type, $condition = null)
    {
        if (substr(strToLower($file), 0, 4) != 'http' && !file_exists($file)) {
            throw new Exception('File "' . $file . '" does not exists');
        }
        $condition = ($condition == null) ? self::NO_CONDITION : $condition;
        if (!isset($this->files[$type])) {
            throw new InvalidArgumentException('File type not found');
        }

        $this->files[$type][$file] = array(
            'file'      => $file,
            'condition' => $condition
        );
    }

    public function remove($file, $type)
    {
        if (!isset($this->files[$type])) {
            throw new InvalidArgumentException('File type not found');
        }
        if (isset($this->files[$type][$file])) {
            unset($this->files[$type][$file]);
        }
    }

    public function replace($file, $file2, $type)
    {
        if (!isset($this->files[$type])) {
            throw new InvalidArgumentException('File type not found');
        }
        $this->files[$type][$file]['file'] = $file2;
    }

    /**
     * Get hash for current scripts
     *
     * @return string hashkey
     */
    public function getFiles($type)
    {
        if (!isset($this->files[$type])) {
            throw new InvalidArgumentException('File type not found');
        }
        $files = array();
        foreach ($this->filters[$type] as $filter) {
            $filter->prepareFiles($this->files[$type]);
        }
        foreach ($this->files[$type] as $file) {
            $isUrl = (strToLower(substr($file['file'], 0, 4)) == 'http');
            if (!$isUrl && !file_exists($file['file'])) {
                throw new Exception('File "' . $file['file'] . '" does not exists');
            }
            if (!isset($files[$file['condition']])) {
                $files[$file['condition']] = array();
            }
            $files[$file['condition']][$file['file']] = $file['file'];
        }
        foreach ($this->filters[$type] as $filter) {
            $filter->modifyFiles($this->files[$type]);
        }
        return $files;
    }

    public static function isExternalUrl($url)
    {
        return (substr(strToLower($url), 0, 4) == 'http');
    }

    public static function filename($file, $folder = null)
    {
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        $fileKey = md5($file);
        if (!$folder) {
            $folder = STATIC_DIR;
        }

        $path  = rtrim($folder, PATH_SEP)  . PATH_SEP;
        $path .= $fileKey{0} . $fileKey{1} . PATH_SEP;
        $path .= $fileKey{2} . $fileKey{3} . PATH_SEP;

        if (!is_dir($path) && !mkdir($path, 0777, true)) {
            throw new Exception('Cant create folder "' . $path . '"');
        }
        return $path . $fileKey . '.' . $ext;
    }

    public static function exists($file)
    {
        $fn = self::filename($file);
        return file_exists($fn);
    }

    public static function save($file, $content, $folder = null)
    {
        $fn = self::filename($file, $folder);
        if (file_exists($fn) && !is_writable($fn)) {
            throw new Exception('Permision denied for write file ' . $fn);
        }
        file_put_contents($fn, $content);
        return $fn;
    }

    /**
     * Copy file to public dir
     */
    public static function copy($file, $folder = null)
    {
        $fn = self::filename($file, $folder);
        if (file_exists($fn) && !is_writable($fn)) {
            throw new Exception('Permision denied for write file ' . $fn);
        }
        if (!copy($file, $fn)) {
            throw new Exception('Can\'t copy "' . $file . '"');
        }
        return $fn;
    }

    public static function link($file)
    {
        $fn = self::filename($file);
        if (!file_exists($fn) && !symlink($file, $fn)) {
            //throw new Exception('Can\'t create symlink from "' . $file . '" to "' . $fn . '"');
            return self::copy($file);
        }
        return $fn;
    }
}
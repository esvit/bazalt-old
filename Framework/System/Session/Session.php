<?php
/**
 * Session class file
 *
 * @category   Session
 * @package    BAZALT/Session
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */

namespace Framework\System\Session;

use \Framework\Core\Logger,
    \Framework\Core\Helper\Url;

/**
 * Клас для роботи з сесіями
 *
 * @category  Session
 * @package   BAZALT/Session
 * @copyright 2010 Equalteam
 * @license   GPLv3
 * @version   $Revision: 133 $
 */
class Session
{
    /**
     * Інстанс Session для сінглтону
     *
     * @var Session|null
     */
    protected static $instance = null;

    /**
     * Імя кукісів по замовчуванню
     */
    const DEFAULT_COOKIE_NAME = 'BAZALT';

    /**
     * Шлях кукісів по замовчуванню
     */
    const DEFAULT_COOKIE_PATH = '/';

    /**
     * Час життя сесії по замовчуванню
     */
    const DEFAULT_TIME_OUT = 300;

    /**
     * Час життя сесії
     */
    protected static $timeOut = self::DEFAULT_TIME_OUT;

    /**
     * Імя кукісів
     */
    protected static $cookieName = self::DEFAULT_COOKIE_NAME;

    /**
     * Шлях кукісів
     */
    protected static $cookiePath = self::DEFAULT_COOKIE_PATH;

    /**
     * Домен кукісів
     */
    protected static $cookieDomain = null;

    /**
     * Обробник сесії
     */
    protected static $handler = 'files';

    /**
     * Шлях для файлів сесії
     */
    protected static $filesHandlerPath = '/tmp';

    /**
     * Опції memcached обробника
     */
    protected static $memcachedServer = null;

    /**
     * Session namespace
     */
    protected $namespace = null;


    /**
     * Флаг, показує чи було викликано старт сесії
     * @var bool
     */
    protected static $isStarted = false;

    /**
     * Constructor
     */
    public function __construct($namespace)
    {
        if (empty($namespace)) {
            throw new \InvalidArgumentException('Session namespace cannot be empty');
        }
        $this->namespace = $namespace;
    }


    /**
     * Повертає єдиниий інстанс Session
     *
     * @return Session
     */
    public static function &getInstance()
    {
        if (self::$instance == null) {
            $className = __CLASS__;
            self::$instance = new $className('bazalt');
            Config_Loader::init('session', self::$instance);
        }
        return self::$instance;
    }

    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * Повератє унікальний ідентифікатор сесії
     *
     * @return string
     */
    public static function getSessionId()
    {
        self::_start();
        return session_id();
    }

    /**
     * Завантажує конфіг
     *
     * @param array $config
     * @throws \Exception
     *
     * @return void
     */
    public function configure($config)
    {
        $this->cookieName = isset($config['name']) ? $config['name'] : self::DEFAULT_COOKIE_NAME;
        $this->cookiePath = (isset($config['cookiePath'])) ? $config['cookiePath'] : self::DEFAULT_COOKIE_PATH;
        $this->timeOut = isset($config['timeOut']) ? $config['timeOut'] : self::DEFAULT_TIME_OUT;
        $this->handler = isset($config['handler']) ? $config['handler'] : 'files';
        $path = isset($config['path']) ? $config['path'] : null;
        if ($path) {
            $this->filesHandlerPath = Config_Loader::replaceConstants($path);
        }
        $this->memcachedServer = isset($config['server']) ? $config['server'] : null;
    }

    /**
     * @return integer the number of seconds after which data will be seen as 'garbage' and cleaned up, defaults to 300 seconds.
     */
    public static function getTimeout()
    {
        self::_start();
        return (int)ini_get('session.gc_maxlifetime');
    }

    /**
     * @param integer $value the number of seconds after which data will be seen as 'garbage' and cleaned up
     */
    public static function setTimeout($value)
    {
        self::$timeOut = $value;
        ini_set('session.gc_maxlifetime',$value);
    }

    /**
     * Стартує сесію
     *
     * @throws \Exception
     */
    private static function _start()
    {
        if (self::$isStarted) {
            return;
        }
        if (STAGE == TESTING_STAGE) {
            echo "ini_set('session.save_handler', " . self::$handler . ");\n";
        }
        ini_set('session.save_handler', self::$handler);
        $currentHandler = ini_get('session.save_handler');

        if ($currentHandler == 'files') {
            $sessionsPath = self::$filesHandlerPath;

            if (!is_dir($sessionsPath) && !mkdir($sessionsPath)) {
                throw new \Exception('Cant create folder "' . $sessionsPath . '"');
            }
            if (!is_writable($sessionsPath)) {
                throw new \Exception('Session dir is not writable "' . $sessionsPath . '"');
            }
            if (STAGE == TESTING_STAGE) {
                echo "ini_set('session.save_path', $sessionsPath);\n";
            }
            ini_set('session.save_path', $sessionsPath);

            Logger::getInstance()->info('Set files handler to folder ' . $sessionsPath);
        } else if ($currentHandler == 'memcache' || $currentHandler == 'memcached') {
            if (!self::$memcachedServer) {
                throw new \Exception('Unknown memcache server, please set it in config');
            }
            if ($currentHandler == 'memcached') {
                self::$memcachedServer = str_replace('tcp://', '', self::$memcachedServer);
            }

            if (STAGE == TESTING_STAGE) {
                echo "ini_set('session.save_path', " . self::$memcachedServer . ");\n";
                echo "ini_set('memcache.session_redundancy', 3);\n";
            }
            ini_set('session.save_path', self::$memcachedServer);
            ini_set('memcache.session_redundancy', 3);

            Logger::getInstance()->info('Set memcache handler to server ' . self::$memcachedServer);
        }

        if (STAGE == TESTING_STAGE) {
            echo "ini_set('session.gc_maxlifetime', " . self::$timeOut . ");\n";
        } else {
            ini_set('session.gc_maxlifetime', self::$timeOut);
        }

        if (defined('SID')) {
            throw new \Exception('Session has already been started by session.auto-start or session_start()');
        }

        if (STAGE == TESTING_STAGE) {
            echo "session_name(" . self::$cookieName . ");\n";
            echo "session_set_cookie_params(" . self::$timeOut . ", " . self::$cookiePath . ", " . self::$cookieDomain . ", false, true);\n";
            echo "session_start();\n";
        }
        session_name(self::$cookieName);
        session_set_cookie_params(self::$timeOut, self::$cookiePath, self::$cookieDomain, false, true);

        // default session gc probability is 1%
        ini_set('session.gc_probability',1);
        ini_set('session.gc_divisor',100);

        if (!@session_start()) {
            throw new \Exception('Session not start');
        }
        Logger::getInstance()->info('Start session ' . session_id() . ' Variables count: ' . count($_SESSION));

        self::$isStarted = true;
    }

    /**
     * Destroy session data
     *
     * @return void
     */
    public function destroy()
    {
        session_destroy();
    }

    /**
     * Генерує унікальний ідентифікатор сесії
     *
     * @return string
     */
    public function regenerateSessionId()
    {
        $this->_start();
        @session_regenerate_id(true);
    }


    /**
     * __set
     *
     * @param string $offset Parameter name
     * @param mixed  $value  Parameter value
     *
     * @throws \Exception
     * @return void
     */
    public function __set($offset, $value)
    {
        if (empty($offset)) {
            throw new \Exception('Session key cann\'t be empty');
        }
//        debug_print_backtrace();exit;
        $this->_start();
        $_SESSION[$this->namespace . '_' . $offset] = $value;
    }

    /**
     * __isset
     *
     * @param string $offset Parameter name
     *
     * @return bool
     */
    public function __isset($offset)
    {
        if (!self::$isStarted && isset($_COOKIE[self::$cookieName])) {
            $this->_start();
        }
        return isset($_SESSION[$this->namespace . '_' . $offset]);
    }

    /**
     * __unset
     *
     * @param string $offset Parameter name
     *
     * @return void
     */
    public function __unset($offset)
    {
        if (!self::$isStarted && isset($_COOKIE[self::$cookieName])) {
            $this->_start();
        }
        unset($_SESSION[$this->namespace . '_' . $offset]);
    }

    /**
     * __get
     *
     * @param string $offset Parameter name
     *
     * @return mixed
     */
    public function __get($offset)
    {
        if (!self::$isStarted && isset($_COOKIE[self::$cookieName])) {
            $this->_start();
        }
        if (!isset($_SESSION[$this->namespace . '_' . $offset])) {
            return null;
        }
        return $_SESSION[$this->namespace . '_' . $offset];
    }
}

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

using('Framework.System.Config');

/**
 * Клас для роботи з сесіями
 *
 * @category  Session
 * @package   BAZALT/Session
 * @copyright 2010 Equalteam
 * @license   GPLv3
 * @version   $Revision: 133 $
 */
class Session extends Object implements Config_IConfigurable
{
    protected static $instance = null;

    /**
     * Імя кукісів по замовчуванню
     */  
    const DEFAULT_COOKIE_NAME   = 'BAZALT';
    
    /**
     * Шлях кукісів по замовчуванню
     */  
    const DEFAULT_COOKIE_PATH   = '/';
    
    /**
     * Час життя сесії по замовчуванню
     */      
    const DEFAULT_TIME_OUT      = 2000;

    /**
     * Час життя сесії
     */          
    protected $timeOut = self::DEFAULT_TIME_OUT;
    
    /**
     * Імя кукісів
     */     
    protected $cookieName = self::DEFAULT_COOKIE_NAME;

    /**
     * Шлях кукісів
     */      
    protected $cookiePath = self::DEFAULT_COOKIE_PATH;

    /**
     * Constructor
     */
    protected function __construct()
    {
        parent::__construct();
    }

    public static function &getInstance()
    {
        if (self::$instance == null) {
            $className = __CLASS__;
            self::$instance = new $className;
            Configuration::init('session', self::$instance);
        }
        return self::$instance;
    }
    
    /**
     * Повератє Time Out сесії
     *
     * @return integer
     */
    public function getTimeOut()
    {
        return $this->timeOut;
    }

    /**
     * Повератє унікальний ідентифікатор сесії
     *
     * @return string
     */
    public function getSessionId()
    {
        return session_id();
    }

    /**
     * Завантажує конфіг
     *
     * @param mixed $node Конфіг
     *
     * @return void
     */
    public function configure($config)
    {
        $cookieName = isset($config['name']) ? $config['name'] : self::DEFAULT_COOKIE_NAME;
        if (isset($config['cookiePath'])) {
            $cookiePath = $config['cookiePath'];
        }
        $this->timeOut = isset($config['timeOut']) ? $config['timeOut'] : self::DEFAULT_TIME_OUT;
        $handler = isset($config['handler']) ? $config['handler'] : 'files';

        if ($cookiePath) {
            $this->cookiePath = $cookiePath;
        }

        if ($cookieName) {
            $this->cookieName = $cookieName;
        }

        if ($handler) {
            ini_set('session.save_handler', $handler);
        }
        $currentHandler = ini_get('session.save_handler');
        $savePath = ini_get('session.save_path');

        if ($currentHandler == 'files') {
            $sessionsPath = TEMP_DIR . '/sessions';

            if ($config['path']) {
                $sessionsPath = Configuration::replaceConstants($config['path']);
            }
            if (!is_dir($sessionsPath) && !mkdir($sessionsPath)) {
                throw new Exception('Cant create folder "' . $sessionsPath . '"');
            }
            if (!is_writable($sessionsPath)) {
                throw new Exception('Session dir is not writable "' . $sessionsPath . '"');
            }
            ini_set('session.save_path', $sessionsPath);

            Logger::getInstance()->info('Set files handler to folder ' . $sessionsPath);
        } else if ($currentHandler == 'memcache' || $currentHandler == 'memcached') {
            $server = $config['server'];
            if (!$server) {
                throw new Exception('Unknown memcache server, please set it in config');
            }
            if ($currentHandler == 'memcached') {
                $server = str_replace('tcp://', '', $server);
            }
            ini_set('session.save_path', $server);
            ini_set('memcache.session_redundancy', 3);

            Logger::getInstance()->info('Set memcache handler to server ' . $server);
        }
        ini_set('session.gc_maxlifetime', $this->timeOut);

        if (defined('SID')) {
             throw new Exception('Session has already been started by session.auto-start or session_start()');
        }

        $params = session_get_cookie_params();
        $domain = $params['domain'];
        if (empty($domain)) {
            $domain = '.' . DataType_Url::getCookieDomain();
        }
        session_name($this->cookieName);
        session_set_cookie_params($this->timeOut, $this->cookiePath, $domain, false, true);

        if (!session_start()) {
            throw new Exception('Session not start');
        }
        Logger::getInstance()->info('Start session ' . session_id() . ' Variables count: ' . count($_SESSION));
    }
    
    /**
     * Генерує унікальний ідентифікатор сесії
     *
     * @return string
     */      
    public function regenerateSessionId()
    {
        session_regenerate_id(true);
    }

    
    /**
     * __set
     *
     * @param string $offset Parametr name
     * @param mixed  $value  Parametr value
     *
     * @return void
     */  
    public function __set($offset, $value)
    {
        if (empty($offset)) {
            throw new Exception('Session key cann\'t be empty');
        }
        $_SESSION[$offset] = $value;
    }

    /**
     * __isset
     *
     * @param string $offset Parametr name
     *
     * @return bool
     */     
    public function __isset($offset)
    {
        return isset($_SESSION[$offset]);
    }

    /**
     * __unset
     *
     * @param string $offset Parametr name
     *
     * @return void
     */      
    public function __unset($offset)
    {
        unset($_SESSION[$offset]);
    }

    /**
     * __get
     *
     * @param string $offset Parametr name
     *
     * @return mixed
     */      
    public function __get($offset)
    {
        if (!isset($_SESSION[$offset])) {
            return null;
        }
        return $_SESSION[$offset];
    }
}

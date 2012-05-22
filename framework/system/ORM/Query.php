<?php
/**
 * Query.php
 *
 * @category   ORM
 * @package    BAZALT
 * @subpackage System
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */

/**
 * ORM_Query
 *
 * @category   ORM
 * @package    BAZALT
 * @subpackage System
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */
class ORM_Query extends Object implements IEventable
{
    /**
     * Евент OnFetchAll
     *
     * @var Event
     */    
    public $eventOnFetchAll = Event::EMPTY_EVENT;

    /**
     * Запит
     */
    protected $query = '';

    /**
     * Поточне підключення
     */
    protected $connection = null;

    /**
     * Параметри запиту
     */
    protected $params = array();

    /**
     * Теги кешу
     */
    protected $cacheTags = array();

    /**
     * Флаг вказує чи кешувати запит
     *
     * @since r1231
     */
    protected $cached = true;

    protected $error = null;

    /**
     * Construct
     * 
     * @param string $sql       SQL запит
     * @param array  $params    Параметри запиту
     * @param array  $cacheTags Теги кешу
     */
    public function __construct($sql = '', $params = array(), $cacheTags = array())
    {
        if ($params != null && !is_array($params)) {
            $params = array($params);
        }
        $this->query = $sql;
        $this->params = $params;
        $this->cacheTags = $cacheTags;
    }

    /**
     * Відключає кешування запиту
     *
     * @since r1231
     */
    public function noCache()
    {
        $this->cached = false;
        return $this;
    }

    /**
     * Повертає масив параметрів для запиту
     *
     * @return array
     */
    protected function getQueryParams()
    {
        return $this->params;
    }

    /**
     * Встановлює підключення до БД для запиту
     *
     * @param ORM_Connection_Abstract $connection Підключення до БД
     *
     * @return void
     */
    public function connection(ORM_Connection_Abstract $connection = null)
    {
        if ($connection !== null) {
            $this->connection = $connection;
            return $this;
        }
        return $this->connection;
    }

    /**
     * Повертає ключ в кеші для даного запиту
     *
     * @return string 
     */
    public function getCacheKey()
    {
        if ($this->connection == null) {
            $this->connection = ORM_Connection_Manager::getConnection();
        }
        return $this->connection->computeCacheKey($this->query, $this->getQueryParams());
    }

    /**
     * Виконує запит та повертає обєкт PDO
     *
     * @return PDO 
     */
    protected function execute()
    {
        $profile = Logger::start(__CLASS__, __FUNCTION__);
        if ($this->connection == null) {
            $this->connection = ORM_Connection_Manager::getConnection();
        }
        $res = $this->connection->query($this->query, $this->getQueryParams());
        $this->error = $this->connection->getErrorInfo();
        Logger::stop($profile);
        return $res;
    }

    /**
     * Виконує запит до БД
     * WARNING! Dont work with select, only on MySQL
     * @return int кількість задіяних рядків
     */
    public function exec($returnCount = true)
    {
        $cacheKey = $this->getCacheKey();
        $cached = ($this->cached) ? Cache::Singleton()->getCache($cacheKey) : false;

        if ($cached !== false && defined('CACHE') && CACHE === true) {
            return $cached;
        }
        
        $res = $this->execute();
        if ($returnCount && $res) {
            $rowCount = $res->rowCount();
            Cache::Singleton()->setCache($cacheKey, $rowCount, Cache::DEFAULT_LIFE_TIME, $this->getCacheTags());
            return $rowCount;
        }
    }

    public function getErrorInfo()
    {
        return $this->error;
    }

    public function toSQL()
    {
        return self::getFullQuery($this->query, $this->params);
    }

    /**
     * Формує повний SQL-запит з усіма заповненими параметрами
     *
     * @return string SQL-запит
     */
    public static function getFullQuery($query, $params)
    {
        $keys = array();
        $values = array();
       
        # build a regular expression for each parameter
        foreach ($params as $key => $value)
        {
            $keys[]   = is_string($key) ? '/:' . $key . '/' : '/[?]/';

            $values[] = is_integer($value) ? intval($value) : '"' . addslashes($value) . '"';
        }
       
        $query = preg_replace($keys, $values, $query, 1, $count);
        return $query;
    }

    /**
     * Повертає один результат вибірки
     *
     * @param string $baseClass Назва моделі
     *
     * @return mixed 
     */
    public function fetch($baseClass = 'stdClass')
    {
        $cacheKey = $this->getCacheKey();
        $cached = ($this->cached) ? Cache::Singleton()->getCache($cacheKey) : false;

        if ($cached !== false && defined('CACHE') && CACHE === true) {
            if ($cached === null) {
                return null;
            }
            $res = $this->fillClass($cached, $baseClass);
            return $res;
        }
        $profile = Logger::start(__CLASS__, __FUNCTION__);
        
        $res = $this->execute();
        $itm = $res->fetch(PDO::FETCH_ASSOC);
        $result = null;
        if ($itm !== false) {
            $result = $this->fillClass($itm, $baseClass);
        }
        $res->closeCursor();

        if ($itm === false) {
            $itm = null;
        }
        Cache::Singleton()->setCache($cacheKey, $itm, Cache::DEFAULT_LIFE_TIME, $this->getCacheTags());

        Logger::stop($profile);
        return $result;
    }

    /**
     * Повертає масив результатів вибірки
     *
     * @param string $baseClass Назва моделі
     *
     * @return array 
     */
    public function fetchAll($baseClass = 'stdClass')
    {
        $cacheKey = $this->getCacheKey();
        $cached = ($this->cached) ? Cache::Singleton()->getCache($cacheKey) : false;

        // restore cache
        if ($cached !== false && defined('CACHE') && CACHE === true) {
            if ($cached === null) {
                return null;
            }
            $res = array();
                 foreach ($cached as &$row) {
                $res []= $this->fillClass($row, $baseClass);
            }
            return $res;
        }
        $profile = Logger::start(__CLASS__, __FUNCTION__);
        
        $result = false;

        $this->OnFetchAll($this, $result, $baseClass);

        if ($result === false) {
            $res = $this->execute();
            $itm = $res->fetchAll(PDO::FETCH_ASSOC);
            $res->closeCursor();
            $result = null;
            $cache = null;
            if ($itm !== false) {
                $result = array();
                $cache = array();
                foreach ($itm as &$row) {
                    $cache []= $row;
                    $result []= $this->fillClass($row, $baseClass);
                }
            }
            
            if ($cache === false) {
                $cache = null;
            }
            // save cache
            Cache::Singleton()->setCache($cacheKey, $cache, Cache::DEFAULT_LIFE_TIME, $this->getCacheTags());
        }

        Logger::stop($profile);
        return $result;
    }

    public function rowCount()
    {
        $sql = $this->toSQL();
        $regex = '/^SELECT\s+(?:ALL\s+|DISTINCT\s+)?(?:.*?)\s+FROM\s+(.*)$/i';
        if (preg_match($regex, $sql, $output) > 0) {
            $countQuery = 'SELECT COUNT(*) AS cnt FROM ' . $output[1];
            $regex = '/^(.*)\s+GROUP\s+BY\s+(.*)$/i';
            if (preg_match($regex, $sql, $output) > 0) {
                $countQuery = 'SELECT COUNT(*) AS cnt FROM (' . $countQuery . ') AS t';
            }
            $q = new ORM_Query($countQuery, PDO::FETCH_NUM);
            $q->connection($this->connection());
            return $q->fetch()->cnt;
        }

        return false;
    }

    /**
     * Створює обєкт класу $class і аповнює даними з масиву $data
     *
     * @param array  $data  Дані
     * @param string $class Назва моделі
     *
     * @return mixed 
     */
    protected function fillClass($data, $class)
    {
        if (!Type::isClassExists($class)) {
            throw new Exception('Unknown class ' . $class);
        }
        $resObj = new $class();
        if ($resObj instanceof ORM_BaseRecord) {
            $resObj->fromArray($data);
        } else {
            foreach ($data as $field => $value) {
                $resObj->$field = $value;
            }
        }
        return $resObj;
    }

    /**
     * Повертає масив інформації про стовпці
     *
     * @return array 
     */
    public function fetchColumnsInfo()
    {
        $res = $this->execute();
        $count = $res->columnCount();
        $info = array();
        for ($i = 0; $i < $count; $i++) {
            $colInfo = $res->getColumnMeta($i);
            $info[$colInfo['name']] = $colInfo;
        }
        return $info;
    }

    protected function getCacheTags()
    {
        return $this->cacheTags;
    }
}

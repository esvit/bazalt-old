<?php
/**
 * Abstract.php
 *
 * @category   ORM
 * @package    BAZALT
 * @subpackage System
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */

/**
 * ORM_Connection_Abstract
 *
 * @category   ORM
 * @package    BAZALT
 * @subpackage System
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */ 
abstract class ORM_Connection_Abstract
{
    /**
     * Connection string
     *
     * @see AbstractConnectionString
     */
    protected $connectionString;

    /**
     * Count of last affected rows
     */
    protected $lastAffectedRows = null;

    /**
     * Last executed query
     */
    protected $lastQuery = null;

    /**
     * Count of executed queries
     */
    protected $queryCount = 0;

    protected static $queries = array();

    /**
     * PDO object
     *
     * @see PDO
     */
    private $_PDOObject = null;

    protected $logger = null;

    /**
     * Constructor
     *
     * @param AbstractConnectionString $str Connection string object
     *
     * @see AbstractConnectionString
     */
    public function __construct(ORM_Adapter_Abstract $str)
    {
        $this->connectionString = $str;
        $this->logger = new Logger(get_class($this));
    }

    public static function getLogQueries()
    {
        return self::$queries;
    }

    /**
     * Return PDO object
     *
     * @return PDO object
     */
    private function _getPDO()
    {
        if ($this->_PDOObject == null) {
            $this->_PDOObject = new PDO(
                $this->connectionString->toPDOConnectionString(), 
                $this->connectionString->getUser(),
                $this->connectionString->getPassword(),
                $this->connectionString->getOptions()
            );

            $queries = $this->connectionString->getInitQueries();
            if ($queries != null && @count($queries) > 0) {
                foreach ($queries as $query) {
                    $this->_PDOObject->query($query);
                }
            }
            $this->_PDOObject->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return $this->_PDOObject;
    }

    public function begin()
    {
        $this->_getPDO()->beginTransaction();
    }

    public function commit()
    {
        $this->_getPDO()->commit();
    }

    public function rollBack()
    {
        $this->_getPDO()->rollBack();
    }

    /**
     * Bind params to PDO
     *
     * @param PDOStatement $st     PDO Statement
     * @param array        $params Params of query
     *
     * @return void
     */
    protected function bindParams(PDOStatement $st, $params = array())
    {
        if (is_array($params)) {
            $num = 1;
            foreach ($params as $value) {
                if (is_int($value)) {
                    $param = PDO::PARAM_INT;
                    $sParam = 'int';
                } elseif(is_bool($value)) {
                    $param = PDO::PARAM_BOOL;
                    $sParam = 'bool';
                } elseif(is_null($value)) {
                    $param = PDO::PARAM_NULL;
                    $sParam = 'null';
                } elseif(is_string($value) || is_float($value)) {
                    $param = PDO::PARAM_STR;
                    $sParam = 'string';
                } else {
                    $param = FALSE;
                    $sParam = 'default';
                }

                $this->logger->info(sprintf('Bind param #%d = "%s" AS %s', $num, $value, $sParam));

                $st->bindValue($num++, $value, $param);
            }
        }
    }

    /**
     * Execute query on database and return count of affected rows
     *
     * @param string $query Query
     *
     * @return int Count of affected rows
     */
    public function exec($query)
    {
        $this->lastQuery = $query;
        Logger::getInstance()->info($query, __CLASS__);
        $this->lastAffectedRows = $this->_getPDO()->exec($query);
        if (STAGE == DEVELOPMENT_STAGE) {
            self::$queries []= $query;
        }
        $this->queryCount++;
        return $this->lastAffectedRows;
    }

    /**
     * Execute query width params on database
     *
     * @param string $query  Query
     * @param array  $params Params of query
     *
     * @return PDOStatement Result of query
     */
    public function query($query, $params = array())
    {
        $this->lastQuery = $query;
        try {
            $profile = Logger::start(__CLASS__, $query);
            $res = $this->_getPDO()->prepare($query);
            if (count($params) > 0) {
                $this->bindParams($res, $params);
            }
            if (STAGE == DEVELOPMENT_STAGE) {
                $this->logger->info(ORM_Query::getFullQuery($query, $params));
            }
            $res->execute();
            Logger::stop($profile);
        } catch (PDOException $ex) {
            throw new ORM_Exception_Query($ex, $query, $params);
        }
        if (STAGE == DEVELOPMENT_STAGE) {
            self::$queries []= $query;
        }
        $this->queryCount++;
        return $res;
    }

    /**
     * Calculate cache key for query with params
     *
     * @param string $query  Query
     * @param array  $params Params of query
     *
     * @return string Cache key
     */
    public function computeCacheKey($query, $params = array())
    {
        $cacheKey = $query . '<' . implode('O_o', $params);
        return $cacheKey;
    }

    /**
     * Return last inserted id
     *
     * @return mixed Last inserted id
     */
    public function getLastInsertId()
    {
        return $this->_getPDO()->lastInsertId();
    }

    public function getErrorInfo()
    {
        return $this->_getPDO()->errorInfo();
    }

    public function getConnectionString()
    {
        return $this->connectionString;
    }
}
<?php
/**
 * Update.php
 *
 * @category   ORM
 * @package    BAZALT
 * @subpackage System
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */

/**
 * ORM_Query_Update
 * Генерує UPDATE запит до БД
 *
 * @category   ORM
 * @package    BAZALT
 * @subpackage System
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */
class ORM_Query_Update extends ORM_Query_Builder
{
    /**
     * Масив полів для запиту
     *
     * @var array
     */
    protected $fields = array();
    
    /**
     * Флаг - видаляти автоматично кеш при апйдейті чи ні
     *
     * @var bool
     */
    protected $autoClearCache = true;

    /**
     * Повертає масив параметрів для запиту
     *
     * @return array
     */
    protected function getQueryParams()
    {
        return array_merge($this->setParams, $this->whereParams);
    }
    
    /**
     * Встановлює флаг $autoClearCache
     *
     * @param mixed    $autoClearCache Значення
     *
     * @return ORM_Query_Update 
     */
    public function autoClearCache($autoClearCache = null)
    {
        if($autoClearCache === null) {
            return $this->autoClearCache;
        }
        $this->autoClearCache = (bool)$autoClearCache;
        return $this;
    }

    /**
     * Генерує SQL для запиту
     *
     * @return string
     */
    public function buildSQL()
    {
        if($this->autoClearCache) {
            Cache::Singleton()->removeByTags($this->getCacheTags());
        }

        $query  = 'UPDATE ' . $this->getFrom();
        $query .= ' SET ';
        $queryVals = '';

        foreach ($this->fields as $field) {
            if (strpos($field, '=') === false) {
                $queryVals  .= $this->connection->quote($field) . ' = ?,';
            } else {
                $queryVals .= $field . ',';
            }
        }
        if (empty($queryVals)) {
            return null;
        }

        $query  .= substr($queryVals, 0, -1) . ' ';
        if (!empty($this->where)) {
            $query .= 'WHERE ' . $this->where . ' ';
        }

        return $query;
    }
}
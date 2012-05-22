<?php
/**
 * Select.php
 *
 * @category   ORM
 * @package    BAZALT
 * @subpackage System
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */

/**
 * ORM_Query_Select
 *
 * @todo Не привязні аліаси до $select та $orderBy полів запиту
 *
 * @category   ORM
 * @package    BAZALT
 * @subpackage System
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */ 
class ORM_Query_Select extends ORM_Query_Builder
{
    /**
     * Початкове значення ліміту
     *
     * @var integer
     */
    protected $limitFrom = null;

    /**
     * Кількість записів в результаті вибірки
     *
     * @var integer
     */
    protected $limitCount = null;

    /**
     * Масив полів, які ввійдуть у результат вибірки
     *
     * @var array
     */
    protected $select = array('*');

    /**
     * Масив ORDER BY параметрів
     *
     * @var array
     */
    protected $orderBy = array();

    /**
     * Масив GROUP BY параметрів
     *
     * @var array
     */
    protected $groupBy = array();

    protected $pageNum = null;

    protected $countOnPage = 10;

    protected $totalCount = null;

    /**
     * Встановлює поля, які ввійдуть у результат вибірки
     *
     * @param array $fields Масив полів, які ввійдуть у результат вибірки
     *
     * @return this
     */
    public function select($fields)
    {
        $this->select = self::explode($fields);
        return $this;
    }

    protected function getCacheTags()
    {
        $from = $this->from;
        if (count($this->joins) > 0) {
            foreach ($this->joins as $join) {
                $from[] = $join->getTable();
            }
        }
        return $from;
    }
    
    /**
     * Генерує SQL для запиту
     *
     * @return string
     */
    public function buildSQL()
    {
		return $this->connection->buildSQL($this);
    }

    /**
     * Встановлює ліміт для запиту
     *
     * @param integer $from  Початкове значення ліміту
     * @param integer $count Кількість записів в результаті
     *
     * @return this
     */
    public function limit($from, $count = null)
    {
        if (!DataType_Number::isValid($from) || (!is_null($count) && !DataType_Number::isValid($count))) {
            throw new InvalidArgumentException();
        }
        $this->limitFrom = $from;
        $this->limitCount = $count;
        return $this;
    }

    /**
     * Встановлює ORDER BY параметри до запиту
     *
     * @param string $fields Список полів для ORDER BY
     *
     * @return this
     */
    public function orderBy($fields)
    {
        $this->orderBy = self::explode($fields);
        return $this;
    }

    /**
     * Додає ORDER BY параметри до запиту
     *
     * @param string $fields Список полів для ORDER BY
     *
     * @return this
     */
    public function addOrderBy($fields)
    {
        $this->orderBy = array_merge($this->orderBy, self::explode($fields));
        return $this;
    }

    /**
     * Додає GROUP BY параметри до запиту
     *
     * @param string $fields Список полів для GROUP BY
     *
     * @return this
     */
    public function groupBy($fields = null)
    {
        if ($fields === null) {
            return $this->groupBy;
        }
        if (!empty($fields)) {
            $this->groupBy = self::explode($fields);
        } else {
            $this->groupBy = array();
        }
        return $this;
    }

    /**
     * Розбиває результати запиту на сторінки і вертає результати заданої сторінки
     *
     * @param int $pageNum     Номер сторінки
     * @param int $countOnPage Кількість запитів на сторінку
     *
     * @return this
     */
    public function page($pageNum = 1, $countOnPage = 10)
    {
        if ($this->pageNum < 1) {
            $this->pageNum = 1;
        }
        $this->pageNum = $pageNum;
        $this->countOnPage = $countOnPage;

        $this->limitFrom = ($this->pageNum - 1) * $countOnPage;
        $this->limitCount = $countOnPage;

        return $this;
    }

    public function fetchAll($baseClass = null)
    {
        $ret = parent::fetchAll($baseClass);

        if ($this->pageNum != null) {
            $query = new ORMQuery('SELECT found_rows() AS `count` -- ' . implode(',', $this->getCacheTags()), array(), $this->getCacheTags());

            $pageCount = $query->fetch();

            $this->totalCount = $pageCount->count;
        }
        return $ret;
    }

    public function totalCount()
    {
        return $this->totalCount;
    }

    public function pageCount()
    {
        $count = ceil($this->totalCount / $this->countOnPage);
        if ($count < 1) {
            return 1;
        }
        return $count;
    }
}
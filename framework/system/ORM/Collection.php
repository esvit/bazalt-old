<?php
/**
 * Collection.php
 *
 * @category   ORM
 * @package    BAZALT
 * @subpackage System
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */

/**
 * ORM_Collection
 *
 * @category   ORM
 * @package    BAZALT
 * @subpackage System
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */ 
class ORM_Collection extends DataType_Array
{
    protected $query = null;

    protected $currentPage = 1;
    
    protected $countPerPage = 10;
    
    protected $pagesCount = 0;
    
    protected $count = 0;

    public function __construct(ORM_Query $query)
    {
        $this->query = $query;
    }
    
    public function getPagesCount()
    {
        return $this->pagesCount;
    }
    
    // depr
    public function getCount()
    {
        return $this->count;
    }

    public function count($count = null)
    {
        if ($count != null) {
            $this->count = $count;
            return $this;
        }
        return $this->count;
    }

    public function orderBy($fields)
    {
        return $this->query->orderBy($fields);
    }
    
    public function addOrderBy($fields)
    {
        return $this->query->addOrderBy($fields);
    }
    
    public function getPage($curPage = 1, $countPerPage = 10)
    {
        return $this->page((int)$curPage)
                    ->countPerPage((int)$countPerPage)
                    ->fetchPage();
    }

    public function fetchPage()
    {
        $curPage = $this->page();
        $q = clone $this->query;

        $this->count = $this->query->rowCount();
        $start = ($curPage-1) * $this->countPerPage;
        if ($this->count > 0 && $start >= $this->count) {
            throw new ORM_Exception_Collection('Invalid page number');
        }
        $q = clone $this->query;
        $q->limit($start, $this->countPerPage);
        $this->pagesCount = ceil($this->count/$this->countPerPage);

        return $q->fetchAll();
    }

    public function countPerPage($countPerPage = null)
    {
        if ($countPerPage != null) {
            $this->countPerPage = $countPerPage;
            return $this;
        }
        return $this->countPerPage;
    }
    
    public function page($page = null)
    {
        if ($page !== null) {
            if ($page < 1) {
                $page = 1;
            }
            $this->currentPage = $page;
            return $this;
        }
        return $this->currentPage;
    }

    /**
     * Формує запит, що рахує позиції елементів
     */
    protected function getOrderQuery()
    {
        $q = clone $this->query;
        $q->from('(SELECT @num := 0) AS rowNumber')
          ->select('@num := @num + 1 AS number, id');

        return ORM::select('*')->from($q);
    }

    /**
     * Дізнається позицію елементу у колекції
     */
    public function getItemOrder($item)
    {
        $newQuery = $this->getOrderQuery();
        $res = $newQuery->andWhere('id = ?', $item->id)
                        ->fetch('stdClass');

        return ($res) ? $res->number : null;
    }

    /**
     * Повертає елемент, який знаходиться після заданого елементу
     */
    public function getNext($item, $limit = 1)
    {
        $order = $this->getItemOrder($item);
        if (!$order) {
            return null;
        }
        $q = $this->getOrderQuery();
        $q->andWhere('number > ?', $order)
          ->orderBy('number ASC');

        if (is_numeric($limit)) {
            $q->limit($limit);
        }
        return $q->fetch(get_class($item));
    }

    /**
     * Повертає елемент, який знаходиться до заданого елементу
     */
    public function getPrev($item, $limit = 1)
    {
        $order = $this->getItemOrder($item);
        if (!$order) {
            return null;
        }
        $q = $this->getOrderQuery();
        $q->andWhere('number < ?', $order)
          ->orderBy('number ASC');

        if (is_numeric($limit)) {
            $q->limit($limit);
        }
        return $q->fetch(get_class($item));
    }

    public function fetchAll()
    {
        return $this->query->fetchAll();
    }

    public function __call($name, $arguments = array())
    {
        return call_user_func_array(array($this->query, $name), $arguments);
    }
}
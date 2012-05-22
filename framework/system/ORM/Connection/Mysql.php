<?php
/**
 * Mysql.php
 *
 * @category   ORM
 * @package    BAZALT
 * @subpackage System
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */

/**
 * ORM_Connection_Mysql
 *
 * @category   ORM
 * @package    BAZALT
 * @subpackage System
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */ 
class ORM_Connection_Mysql extends ORM_Connection_Abstract
{
    public function buildSQL(ORM_Query_Builder $builder)
    {
        $query  = 'SELECT ';

        if ($builder->PageNum != null) {
            $query .= 'SQL_CALC_FOUND_ROWS ';
        }
        $query .= '' . implode(',', $builder->Select) . ' ';
        $query .= 'FROM ' . $builder->From . ' ';
        if (count($builder->Joins) > 0) {
            foreach ($builder->Joins as $join) {
                $query .= ' ' . $join->toSQL();
            }
        }

        $where = $builder->Where;
        if (!empty($where)) {
            $query .= 'WHERE ' . $where . ' ';
        }

        $groupBy = $builder->GroupBy;
        if (count($groupBy) > 0) {
            $query .= 'GROUP BY ' . implode(',', $groupBy) . ' ';
        }

        $orderBy = $builder->OrderBy;
        if (count($orderBy) > 0) {
            $query .= 'ORDER BY ' . implode(',', $orderBy) . ' ';
        }

        $limitCount = $builder->LimitCount;
        $limitFrom = $builder->LimitFrom;
        if (isset($limitFrom)) {
            $query .= 'LIMIT ' . $limitFrom . (isset($limitCount) ? ', '.$limitCount : '' );
        }
        return $query;
    }

    public function quote($string)
    {
        return '`' . $string . '`';
    }
}
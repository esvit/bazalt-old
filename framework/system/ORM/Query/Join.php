<?php
/**
 * Join.php
 *
 * @category   ORM
 * @package    BAZALT
 * @subpackage System
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */

/**
 * ORM_Query_Join
 *
 * @category   ORM
 * @package    BAZALT
 * @subpackage System
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */
class ORM_Query_Join extends ORM_Query_Builder
{
    /**
     * Умова вибірки
     *
     * @var string
     */
    const LEFT_JOIN = 'LEFT';

    /**
     * Умова вибірки
     *
     * @var string
     */
    const RIGHT_JOIN = 'RIGHT';

    /**
     * Умова вибірки
     *
     * @var string
     */
    const INNER_JOIN = 'INNER';

    /**
     * Умова вибірки
     *
     * @var string
     */
    const OUTER_JOIN = 'OUTER';

    /**
     * Умова вибірки
     *
     * @var string
     */
    protected $from;

    /**
     * Умова вибірки
     *
     * @var string
     */
    protected $alias;

    /**
     * Умова вибірки
     *
     * @var string
     */
    protected $conditions = array();

    /**
     * Умова вибірки
     *
     * @var string
     */
    protected $type;

    /**
     * Повертає тип JOIN запиту
     *
     * @return string
     */
    public function getJoinType()
    {
        return $this->type;
    }

    /**
     * Сonstruct
     * 
     * @param string $name       Назва моделі, для якої виконується JOIN запит
     * @param array  $conditions Масив умов, буде підставлено в ON
     * @param string $type       Тип JOIN
     */
    public function __construct($name, $conditions = array(), $type = self::LEFT_JOIN)
    {
        $this->type = $type;

        $name = trim($name);
        $names = explode(' ', $name);
        $tableName = $names[0];

        if (count($names) == 1) {
            $alias = $this->generateAlias($tableName);
        } else if (count($names) > 1) {
            if ($names[1] == 'ON') {
                $alias = $this->generateAlias($tableName);
            } else {
                $alias = $names[1];
            }
        } else {
            // trigger_error('asdas');
            throw new InvalidArgumentException();
        }

        $this->from = $tableName;
        $this->alias = $alias;

        if (count($conditions) > 0) {
            $this->conditions = $conditions;
        } else {
            $name = explode('ON', $name);
            $this->conditions = ' ON ' . $name[1] . ' ';
        }

        parent::__construct();
    }
    
    public function getTable()
    {
        $table = ORM_Record::getTableName($this->from);
        if (empty($table)) {
            throw new Exception('Invalid model "' . $this->from . '"');
        }
        return $table;
    }

    /**
     * Генерує SQL для запиту
     *
     * @return string
     */
    public function buildSQL()
    {
        $query  = $this->getJoinType() . ' JOIN ';
        $query .=  $this->getTable() . ' AS ' . $this->alias;

        if (is_array($this->conditions)) {
            $query .= ' ON ' . $this->alias . '.' . $this->conditions[0] . ' = ' . $this->conditions[1] . ' ';
        } else {
            $query .= $this->conditions;
        }
        return $query;
    }
}
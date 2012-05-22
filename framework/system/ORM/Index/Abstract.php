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
 * ORM_Index_Abstract
 *
 * @category   ORM
 * @package    BAZALT
 * @subpackage System
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */ 
abstract class ORM_Index_Abstract
{
    /**
     * Назва індексу
     */
    protected $name = null;
    
    /**
     * Поля індексу
     */
    protected $fields = array();

    /**
     * Construct
     * 
     * @param array $name   Назва індексу
     * @param array $fields Поля індексу
     */
    public function __construct($name, $fields = array())
    {
        $this->name = $name;
        $this->fields = $fields;
    }

    /**
     * Повертає SQL для Create Table
     *
     * @return string 
     */
    public function toSql()
    {
        return 'INDEX `'.$this->name.'` (`'.implode('`, `', $this->fields).'`)';
    }

    public function name($name = null)
    {
        return $this->name;
    }
}

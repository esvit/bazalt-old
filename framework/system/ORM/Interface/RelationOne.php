<?php
/**
 * Record.php
 *
 * @category   ORM
 * @package    BAZALT
 * @subpackage System
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */

/**
 * ORM_Interface_RelationOne
 *
 * @category   ORM
 * @package    BAZALT
 * @subpackage System
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */ 
interface ORM_Interface_RelationOne extends Iterator
{
    /**
     * Get record connected with current record
     *
     * @return ORM_Record
     */    
    function get();

    /**
     * Set new record connected with current record
     *
     * @param ORM_Record &$item New record
     *
     * @return void
     */    
    function set(ORM_Record &$item);
}
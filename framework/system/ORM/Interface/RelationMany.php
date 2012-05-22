<?php
/**
 * RelationMany.php
 *
 * @category   ORM
 * @package    BAZALT
 * @subpackage System
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */

/**
 * ORM_Interface_RelationMany
 *
 * @category   ORM
 * @package    BAZALT
 * @subpackage System
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */ 
interface ORM_Interface_RelationMany extends Iterator
{
    /**
     * Робить вибірку усіх записів
     *
     * @return array Array of ORMRecord
     * @see ORMRecord
     */
    function getAll();

    /**
     * Створює зв'язок між поточним обєктом та обєктом $item
     *
     * @param ORM_Record $item об'єкт, який потрібно додати
     *
     * @return void
     */
    function add(ORM_Record $item);

    /**
     * Видаляє зв'язок між поточним обєктом та обєктом $item
     *
     * @param ORM_Record $item об'єкт, який потрібно видалити
     *
     * @return void
     */    
    function remove(ORM_Record $item);

    /**
     * Перевіряє чи існує зв'язок між поточним обєктом та обєктом $item
     *
     * @param ORM_Record $item об'єкт, який потрібно перевірити
     *
     * @return bool
     */       
    function has(ORM_Record $item);
}
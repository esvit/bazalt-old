<?php
/**
 * FullText.php
 *
 * @category   ORM
 * @package    BAZALT
 * @subpackage System
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */

/**
 * ORM_Index_FullText
 *
 * @category   ORM
 * @package    BAZALT
 * @subpackage System
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */ 
class ORM_Index_FullText extends ORM_Index_Abstract
{
    /**
     * Повертає SQL для Create Table
     *
     * @return string 
     */
    public function toSql()
    {
        return 'FULLTEXT '.parent::toSql();
    }
}

<?php
/**
 * IAbstractAdapter
 *
 * @category   Core
 * @package    BAZALT
 * @subpackage Interfaces
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    SVN: $Revision: 156 $
 * @link       http://bazalt-cms.com/
 */

/**
 * IAbstractAdapter
 *
 * @category   Core
 * @package    BAZALT
 * @subpackage Interfaces
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    Release: $Revision: 156 $
 */
interface IAbstractAdapter
{
    /**
     * Конструктор
     *
     * @param Adaptee $adaptee Клас, унаслідуваний від класу Adaptee
     * @param array   $options Опції адаптеру
     */
    function __construct($adaptee, $options = array());
}
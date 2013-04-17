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

namespace Framework\Core\Interfaces;
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
interface AbstractAdapter
{
    /**
     * Конструктор
     *
     * @param Adaptee $adaptee Клас, унаслідуваний від класу Adaptee
     * @param array   $options Опції адаптеру
     */
    function __construct($adaptee, $options = array());
}
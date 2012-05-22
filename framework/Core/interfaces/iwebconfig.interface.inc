<?php
/**
 * IWebConfig
 *
 * @category   Core
 * @package    BAZALT
 * @subpackage Interfaces
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    SVN: $Revision: 154 $
 * @link       http://bazalt-cms.com/
 */

/**
 * IWebConfig
 *
 * @category   Core
 * @package    BAZALT
 * @subpackage Interfaces
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    Release: $Revision: 154 $
 */
interface IWebConfig extends ISingleton
{
    /**
     * Викликається для завантаження конфігурації класу
     *
     * @param XmlNode $node xml елемент конфігурації даного класу
     * @return void
     */
    function loadWebConfig($node);
}
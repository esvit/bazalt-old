<?php
/**
 * Exception_Singleton
 *
 * @category   Core
 * @package    BAZALT
 * @subpackage Exception
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    SVN: $Revision: 152 $
 * @link       http://bazalt-cms.com/
 */

/**
 * Exception_Singleton
 *
 * @category   Core
 * @package    BAZALT
 * @subpackage Exception
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    Release: $Revision: 152 $
 */
final class Exception_Singleton extends Exception_Base
{
    public function __construct($message = 'Can\'t create singleton.')
    {
        parent::__construct($message);
    }
}
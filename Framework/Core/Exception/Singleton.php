<?php
/**
 * Exception_Singleton
 *
 * @category   Core
 * @package    Core
 * @subpackage Exception
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    SVN: $Revision: 152 $
 * @link       http://bazalt-cms.com/
 */

namespace Framework\Core\Exception;

/**
 * Exception_Singleton
 *
 * @category   Core
 * @package    Core
 * @subpackage Exception
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    Release: $Revision: 152 $
 */
final class Singleton extends Base
{
    public function __construct($message = 'Can\'t create singleton.')
    {
        parent::__construct($message);
    }
}
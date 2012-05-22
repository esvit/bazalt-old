<?php
/**
 * Exception_Base
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
 * Exception_Base
 *
 * @category   Core
 * @package    BAZALT
 * @subpackage Exception
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    Release: $Revision: 152 $
 */
abstract class Exception_Base extends Exception
{
    protected $innerException = null;

    public function __construct($message, $innerEx = null, $code = 0)
    {
        if (version_compare(PHP_VERSION, '5.3.0', '>=')) {
            parent::__construct($message, $code, $innerEx);
        } else {
            parent::__construct($message, $code);
        }
        $this->innerException = $innerEx;
    }

    public function getInnerException()
    {
        return $this->innerException;
    }

    public function getDetails()
    {
        return null;
    }
}
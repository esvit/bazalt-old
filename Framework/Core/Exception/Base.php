<?php
/**
 * Exception_Base
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
 * Exception_Base
 *
 * @category   Core
 * @package    Core
 * @subpackage Exception
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    Release: $Revision: 152 $
 */
abstract class Base extends \Exception
{
    /**
     * Inner exception
     *
     * @var null
     */
    protected $innerException = null;

    /**
     * Constructor
     *
     * @param string $message Text message
     * @param null   $innerEx Inner exception
     * @param int    $code    Exception code
     */
    public function __construct($message, $innerEx = null, $code = 0)
    {
        if (version_compare(PHP_VERSION, '5.3.0', '>=')) {
            parent::__construct($message, $code, $innerEx);
        } else {
            parent::__construct($message, $code);
        }
        $this->innerException = $innerEx;
    }

    /**
     * Exception detail information
     *
     * @return null
     */
    public function getDetails()
    {
        return null;
    }
}
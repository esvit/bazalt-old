<?php
/**
 * Exception_Event
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
 * Exception_Event
 *
 * @category   Core
 * @package    BAZALT
 * @subpackage Exception
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    Release: $Revision: 152 $
 */
final class Exception_Event extends Exception_Base
{
    const UNDEFINED = 0;
    const INVALID_HANDLER = 1;

    const UNDEFINED_MESSAGE = 'Undefined event "%s".';
    const INVALID_HANDLER_MESSAGE = 'Invalid event handler.';

    public function __construct($eventName, $code = self::UNDEFINED)
    {
        $message = '';

        switch ($code) {
            case self::UNDEFINED:
                $message = sprintf(self::UNDEFINED_MESSAGE, $eventName);
                break;
            case self::INVALID_HANDLER:
                $message = sprintf(self::INVALID_HANDLER_MESSAGE, $eventName);
                break;
        }
        parent::__construct($message, null, $code);
    }
}
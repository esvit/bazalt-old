<?php
/**
 * Exception_Property
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
 * Exception_Property
 *
 * @category   Core
 * @package    BAZALT
 * @subpackage Exception
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    Release: $Revision: 152 $
 */
final class Exception_Property extends Exception_Base
{
    const UNDEFINED = 0;
    const READONLY = 1;

    const UNDEFINED_MESSAGE = 'Undefined property "%s".';
    const READONLY_MESSAGE = 'The property "%s" is readonly.';

    public function __construct($property, $code = self::UNDEFINED)
    {
        $message = '';

        switch ($code) {
            case self::UNDEFINED:
                $message = sprintf(self::UNDEFINED_MESSAGE, $property);
                break;
            case self::READONLY:
                $message = sprintf(self::READONLY_MESSAGE, $property);
                break;
        }
        parent::__construct($message, null, $code);
    }
}
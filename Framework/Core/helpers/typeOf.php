<?php
/**
 * typeOf
 *
 * @category   Core
 * @package    Core
 * @subpackage Helpers
 * @author     Vitalii Savchuk <esvit666@gmail.com>
 * @author     Alex Slubsky <aslubsky@gmail.com>
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version    SVN: $Rev: 110 $
 * @link       http://bazalt.org.ua/
 */

use Framework\Core\Type;

/**
 * Return type of variable
 *
 * @param mixed $mixed Variable
 *
 * @return string | Type
 */
function typeOf($mixed)
{
    $type = gettype($mixed);
    if ($type == 'object' || class_exists($mixed)) {
        return new Type($mixed);
    }
    return $type;
}
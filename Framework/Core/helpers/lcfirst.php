<?php
/**
 * lcfirst
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

/**
 * Set first char to lower case
 *
 * @param string $str String
 *
 * @return string
 */
function lcfirst($str)
{
    $str{0} = strtolower($str{0});
    return $str;
}
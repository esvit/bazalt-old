<?php
/**
 * getCalledClass
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
 * getCalledClass function for v 5.2
 *
 * @return string
 */
function getCalledClass()
{
    $bt = debug_backtrace();

    $level = 0;
    do {
        $trace = $bt[++$level];
        $lines = file($trace['file']);
        $callerLine = trim($lines[$trace['line'] - 1]);
        $pattern = '/([a-zA-Z0-9\_]+)[ \t]*::[ \t]*' . $trace['function'] . '/';

        preg_match($pattern, $callerLine, $matches);
    } while (count($matches) > 1 && $matches[1] == 'parent');

    if (isset($matches[1])) {
        return $matches[1];
    }
    return null;
}
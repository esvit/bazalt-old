<?php
/**
 * getCalledClass
 *
 * PHP versions 5
 *
 * LICENSE:
 * 
 * This library is free software; you can redistribute it
 * and/or modify it under the terms of the GNU Lesser General
 * Public License as published by the Free Software Foundation;
 * either version 2.1 of the License, or (at your option) any
 * later version.
 * 
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @category   Core
 * @package    BAZALT
 * @subpackage Functions
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
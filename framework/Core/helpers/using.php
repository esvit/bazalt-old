<?php
/**
 * using
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
 * Require namespace $name
 *
 * @param string $name Namespace name
 *
 * @return void
 */
function using($name)
{
    $lib = Library::create($name);
    if ($lib == null) {
        throw new Exception('Unknown namespace ' . $name);
    }
    $fileName = $lib->getFilename();

    if (!empty($fileName) && is_readable($fileName)) {
        require_once $fileName;
    }
}

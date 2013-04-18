<?php
/**
 * Абстрактиний драйвер сесій
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
 * @category   Locale
 * @package    BAZALT/Locale
 * @subpackage Adapters
 * @author     Vitalii Savchuk <esvit666@gmail.com>
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version    SVN: $Rev: 20 $
 * @link       http://www.bazalt.org.ua/
 */

/**
 * Абстрактиний драйвер сесій
 *
 * @category   Locale
 * @package    BAZALT/Locale
 * @subpackage Adapters
 * @author     Vitalii Savchuk <esvit666@gmail.com>
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version    Release: $Rev: 20 $
 * @link       http://www.bazalt.org.ua/
 */
abstract class Locale_Translation_AbstractAdapter implements IAbstractAdapter
{
    public function __construct($adaptee, $options = array())
    {
        
    }

    function raiseError($error = null, $code = null)
    {
        echo $error, $code;
    }

    abstract function initLocale(Locale_Config $locale, $domain = null);

    abstract function getTranslation($string, $domain = null);

    abstract function getPluralTranslation($string, $pluralString, $n = 0, $domain = null);

    abstract function bindTextDomain($file, $domain = null);

    abstract function readDictionary($name, $folder, $locale = null);

    abstract function saveDictionary(Locale_Translation_Dictionary $dict, $folder = null, $locale = null);
}
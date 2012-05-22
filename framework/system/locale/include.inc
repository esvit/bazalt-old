<?php
/**
 * Locale
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
 * @category  Locale
 * @package   BAZALT/Locale
 * @author    Vitalii Savchuk <esvit666@gmail.com>
 * @license   http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version   SVN: $Rev: 20 $
 * @link      http://www.bazalt.org.ua/
 */

/**#@+ Constants **/
define('LOCALE_NUMBER',                     'number');
define('LOCALE_CURRENCY',                   'currency');
define('LOCALE_DATE',                       'date');
define('LOCALE_TIME',                       'time');
define('LOCALE_DATETIME',                   'datetime');

define('LOCALE_NUMBER_FLOAT' ,              'float');
define('LOCALE_NUMBER_INTEGER' ,            'integer');

define('LOCALE_CURRENCY_LOCAL',             'local');
define('LOCALE_CURRENCY_INTERNATIONAL',     'international');

define('LOCALE_DATETIME_SHORT',             'short');
define('LOCALE_DATETIME_DEFAULT',           'default');
define('LOCALE_DATETIME_MEDIUM',            'medium');
define('LOCALE_DATETIME_LONG',              'long');
define('LOCALE_DATETIME_FULL',              'full');

define('LOCALE_ALL_COUNTRIES',              'all');
define('LOCALE_AFRICAN_COUNTRIES',          'afr');
define('LOCALE_ASIAN_COUNTRIES',            'asi');
define('LOCALE_EUROPEAN_COUNTRIES',         'eur');
define('LOCALE_NORTHAMERICAN_COUNTRIES',    'nam');
define('LOCALE_SOUTHAMERICAN_COUNTRIES',    'sam');
define('LOCALE_OCEANIAN_COUNTRIES',         'oce');
/**#@-*/

Core_Autoload::registerNamespace('Locale', dirname(__FILE__));
    
class LocaleInfo extends Locale_Info { }
class LocaleFormat extends Locale_Format { }

require_once 'Locale.php';
require_once 'helpers/__.php';
require_once 'helpers/_p.php';
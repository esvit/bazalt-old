<?php
/**
 * Data types
 *
 * @category   Core
 * @package    BAZALT
 * @subpackage DataType
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    SVN: $Revision: 152 $
 * @link       http://bazalt-cms.com/
 */

namespace Framework\Core\Helper;

define ('EXT_MBSTRING', extension_loaded('mbstring'));

// Check mbstring extension
if (!EXT_MBSTRING) {
    die('System require mbstring extension');
}

// set default mbstring encoding, system work only with UTF-8
mb_internal_encoding('UTF-8');

// include datatypes
require_once 'Abstract.php';
require_once 'Array.php';
require_once 'String.php';
require_once 'Guid.php';
require_once 'Number.php';
require_once 'Namespace.php';
require_once 'Url.php';

require_once 'Collection.php';
require_once 'Storage.php';
require_once 'Manager.php';
require_once 'Adaptee.php';
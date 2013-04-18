<?php
/**
 * using
 *
 * @category   Core
 * @package    Core
 * @subpackage Helpers
 * @author     Vitalii Savchuk <esvit666@gmail.com>
 * @author     Alex Slubsky <aslubsky@gmail.com>
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version    SVN: $Rev: 110 $
 * @link       http://bazalt-cms.com/
 */

use Framework\Core\Library;

/**
 * Require namespace $name
 *
 * @param string $name Namespace name
 *
 * @throws Exception
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

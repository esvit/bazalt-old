<?php
/**
 * DataType_Guid
 *
 * @category   Core
 * @package    Core
 * @subpackage DataType
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    SVN: $Revision: 178 $
 * @link       http://bazalt-cms.com/
 */

namespace Framework\Core\Helper;

/**
 * DataType_Guid
 *
 * @category   Core
 * @package    Core
 * @subpackage DataType
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */
class Guid
{
    /**
     * Determine if supplied string is a valid GUID
     *
     * @param string $guid String to validate
     * @return boolean
     */
    public static function isValid($guid)
    {
        return !empty($guid) &&
                preg_match('/^\{?[A-Z0-9]{8}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{12}\}?$/i', $guid);
    }

    public static function newGuid()
    {
        $guid = sprintf('%04x%04x-%04x-%03x4-%04x-%04x%04x%04x',
            mt_rand(0, 65535), mt_rand(0, 65535), // 32 bits for "time_low"
            mt_rand(0, 65535), // 16 bits for "time_mid"
            mt_rand(0, 4095),  // 12 bits before the 0100 of (version) 4 for "time_hi_and_version"
            bindec(substr_replace(sprintf('%016b', mt_rand(0, 65535)), '01', 6, 2)),
            // 8 bits, the last two of which (positions 6 and 7) are 01, for "clk_seq_hi_res"
            // (hence, the 2nd hex digit after the 3rd hyphen can only be 1, 5, 9 or d)
            // 8 bits for "clk_seq_low"
            mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535) // 48 bits for "node"
        );
        return $guid;
    }
}
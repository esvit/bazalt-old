<?php
/**
 * Number
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
 * Number
 *
 * @category   Core
 * @package    Core
 * @subpackage DataType
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */
class Number
{
    public static function isValid($value)
    {
        return is_numeric($value);
    }

    public static function isValidStrict($value, $allowDecimals = false, $allowZero = false, $allowNeg = false)
    {
        if ($allowDecimals) {
            $regex = $allowZero ?
                    '[0-9]+(\.[0-9]+)?': 
                    '(^([0-9]*\.[0-9]*[1-9]+[0-9]*)$)|(^([0-9]*[1-9]+[0-9]*\.[0-9]+)$)|(^([1-9]+[0-9]*)$)';
        } else {
            $regex = $allowZero ?
                    '[0-9]+':
                    '[1-9]+[0-9]*';
        }
        return preg_match('#^' . ($allowNeg ? '\-?' : '') . $regex . '$#', $str);
    }

    /**
     * Return human readable sizes
     *
     * @param int    $size      size in bytes
     * @param string $max       maximum unit
     * @param string $retstring return string format
     */
    public static function sizeReadable($size, $max = null, $retstring = '%01.2f %s')
    {
        $prefix = array('B', 'K', 'MB', 'GB', 'TB', 'PB');
        $iSize  = 1000;

        // Max unit to display
        $depth = count($prefix) - 1;
        if ($max && ($num = array_search($max, $prefix)) !== false) {
            $depth = $num;
        }

        $i = 0;
        while ($size >= $iSize && ++$i < $depth) {
            $size /= $iSize;
        }
        return sprintf($retstring, $size, $prefix[$i]);
    }
}
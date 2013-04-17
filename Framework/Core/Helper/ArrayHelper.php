<?php
/**
 * DataType_Abstract
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
 * DataType_Abstract
 *
 * @category   Core
 * @package    Core
 * @subpackage DataType
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */
final class ArrayHelper
{
    public static function deepArrayMap($func, $arr)
    {
        $newArr = array();
        foreach ($arr as $key => $value) {
            $newArr[$key] = (is_array($value) ? self::deepArrayMap($func, $value) : call_user_func($func, $value));
        }
        return $newArr;
    }

    /**
     * Sort array
     *
     * @param array  $array     the array we want to sort
     * @param string $clause    a string specifying how to sort the array similar to SQL ORDER BY clause
     * @param bool   $ascending that default sorts fall back to when no direction is specified
     *
     * @return null
     */
    public static function sortBy(&$array, $clause, $ascending = true)
    {
        $clause = preg_replace('/\s+/', ' ', $clause);
        $keys = explode(',', $clause);
        $dirMap = array('desc' => 1, 'asc' => -1);
        $def = $ascending ? -1 : 1;

        $keyAry = array();
        $dirAry = array();
        foreach ($keys as $key) {
            $key = explode(' ', trim($key));
            $keyAry[] = trim($key[0]);
            if (isset($key[1])) {
                $dir = strToLower(trim($key[1]));
                $dirAry[] = $dirMap[$dir] ? $dirMap[$dir] : $def;
            } else {
                $dirAry[] = $def;
            }
        }

        $fnBody = '';
        for ($i = count($keyAry) - 1; $i >= 0; $i--) {
            $k = $keyAry[$i];
            $direction = $dirAry[$i];
            $aStr = '$a[\'' . $k . '\']';
            $bStr = '$b[\'' . $k . '\']';

            if (strpos($k, '(') !== false) {
                $aStr = '$a->' . $k;
                $bStr = '$b->' . $k;
            }
            if (strpos($k, '->') !== false) {
                $aStr = '$a->{"' . str_replace('->', '', $k) . '"}';
                $bStr = '$b->{"' . str_replace('->', '', $k) . '"}';
            }
            if (empty($fnBody)) {
                $fnBody = 'return 0;';
            }

            $fnBody = 'if(' . $aStr . ' == ' . $bStr . ') { ' . $fnBody . ' }' . "\n";
            $fnBody .= 'return (' . $aStr . ' < ' . $bStr . ') ? ' . $direction . ' : ' . (-1 * $direction) . ';' . "\n";
        }

        if (!empty($fnBody)) {
            $sortFn = create_function('$a,$b', $fnBody);
            uasort($array, $sortFn);       
        }
    }
}
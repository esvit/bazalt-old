<?php
/**
 * DataType_Abstract
 *
 * @category  Core
 * @package   BAZALT
 * @copyright 2010 Equalteam
 * @license   GPLv3
 * @version   SVN: $Revision: 178 $
 * @link      http://bazalt-cms.com/
 */

/**
 * DataType_Abstract
 *
 * @category   Core
 * @package    BAZALT
 * @subpackage DataType
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */
class DataType_Array extends DataType_Abstract implements Iterator, ArrayAccess, Countable
{
    protected $items = array();

    public function rewind()
    {
        reset($this->items);
    }

    public function current()
    {
        return current($this->items);
    }

    public function key()
    {
        return key($this->items);
    }

    public function next()
    {
        return next($this->items);
    }

    public function valid()
    {
        return key($this->items) !== null;
    }

    public function count()
    {
        return count($this->items);
    }

    public function offsetSet($offset, $value)
    {
        if ($offset) {
            $this->items[$offset] = $value;
        } else {
            $this->items[] = $value;
        }
    }

    public function offsetExists($offset)
    {
        if (!is_array($this->items)) {
            return false;
        }
        return array_key_exists($offset, $this->items);
    }

    public function offsetUnset($offset)
    {
        unset($this->items[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->offsetExists($offset) ? $this->items[$offset] : null;
    }

    public function sort($clause, $ascending = true)
    {
        self::sortBy($this->items, $clause, $ascending);
    }

    /**
     * Return items of collection
     *
     * @param object $var Not using, just for Strict mode
     *
     * @return array
     */
    public function toArray($var = null)
    {
        return $this->items;
    }

    public function addRange($arr)
    {
        if (!is_array($arr)) {
            throw new InvalidArgumentException();
        }
        $this->items = array_merge_recursive($this->items, $arr);
    }

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
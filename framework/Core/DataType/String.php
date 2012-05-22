<?php
/**
 * String
 *
 * @category   Core
 * @package    BAZALT
 * @subpackage DataType
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    SVN: $Revision$
 * @link       http://bazalt-cms.com/
 */

/**
 * Тип даних - строка
 *
 * @category   Core
 * @package    BAZALT
 * @subpackage DataType
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    Release: $Revision$
 */
class DataType_String extends DataType_Abstract
{
    protected $length;

    public function __construct($value = '')
    {
        if (is_object($value)) {
            $this->value = $value->__toString();
        } else {
            $this->value = $value;
        }
        $this->length = mb_strlen($value);
        parent::__construct();
    }

    public static function isValid($str)
    {
        return (is_string($str) ||
               (is_object($str) && ($str instanceof DataType_String)));
    }

    /**
    * Replace constants in string (like {FRAMEWORK_DIR})
    *
    * @param    string  $str    Input string
    *
    * @return   string  String with replaced constants
    */
    public static function replaceConstants($str, $constants = null)
    {
        if (strpos($str, '{') === false && strpos($str, '}') === false) {
            return $str;
        }

        $newStr = '';
        $start = 1;
        while (strlen($str) != 0) {
            $start = strpos($str, '{');
            if ($start > 0) {
                $newStr .= substr($str, 0, $start);
            }
            $end = strpos($str, '}', $start + 1);
            if ($end !== false && $start < $end) {
                $const = substr($str, $start + 1, $end - $start - 1);
                $str = substr($str, $end + 1);

                // if element exists in array or defined constant
                $newStr .= (is_array($constants) && array_key_exists($const, $constants)) ? 
                                $constants[$const] : 
                                (defined($const) ? constant($const) : '{' . $const . '}');
            } else {
                $newStr .= substr($str, $start);
                break;
            }
        }
        return $newStr;
    }

    /**
    * Translates a camel case string into a string with underscores 
    * (e.g. firstName -> first_name)
    *
    * @param  string $str String in camel case format
    * @return string Translated into underscore format
    */
    public static function fromCamelCase($str)
    {
        $str[0] = strtolower($str[0]);
        $func = create_function('$c', 'return "_" . strToLower($c[1]);');
        $return = preg_replace_callback('/([A-Z])/', $func, $str);
        return $return;
    }

    /**
    * Translates a string with underscores into camel case 
    * (e.g. first_name -> firstName)
    *
    * @param  string $str          String in underscore format
    * @param  bool   $capFirstChar If true, capitalise the first char in $str
    * @return string Translated into camel caps
    */
    public static function toCamelCase($str, $capFirstChar = false)
    {
        if ($capFirstChar) {
            $str[0] = strToUpper($str[0]);
        }
        $func = create_function('$c', 'return strtoupper($c[1]);');
        $return = preg_replace_callback('/_([a-z])/', $func, $str);
        return $return;
    }

    /**
     * Replace arguments in a string with their values. Arguments are represented by {#}.
     *
     * @param  string Source string
     * @param  mixed  Arguments, can be passed in an array or through single variables.
     * @return string Modified string
     */
    public static function format($str)
    {
        $args = array();
        $p = 0;

        for ($i = 1; $i < func_num_args(); $i++) {
            $arguments = func_get_arg($i);

            if (is_array($arguments)) {
                foreach ($arguments as $argument) {
                    $args[$p++] = $argument;
                }
            } else {
                $args[$p++] = $arguments;
            }
        }
        return self::replaceConstants($str, $args);
    }

    /**
     * Chop a string into a smaller string.
     *
     * @author  Aidan Lister <aidan@php.net>
     * @version 1.1.0
     * @link    http://aidanlister.com/repos/v/function.str_chop.php
     * @param   mixed $string The string you want to shorten
     * @param   int   $length The length you want to shorten the string to
     * @param   bool  $center If true, chop in the middle of the string
     * @param   mixed $append String appended if it is shortened
     */
    public static function chop($string, $length = 60, $center = false, $append = null)
    {
        // Set the default append string
        if ($append === null) {
            $append = ($center === true) ? ' ... ' : ' ...';
        }
     
        // Get some measurements
        $len_string = strlen($string);
        $len_append = strlen($append);
     
        // If the string is longer than the maximum length, we need to chop it
        if ($len_string > $length) {
            // Check if we want to chop it in half
            if ($center === true) {
                // Get the lengths of each segment
                $len_start = $length / 2;
                $len_end = $len_start - $len_append;
     
                // Get each segment
                $seg_start = substr($string, 0, $len_start);
                $seg_end = substr($string, $len_string - $len_end, $len_end);
     
                // Stick them together
                $string = $seg_start . $append . $seg_end;
            } else {
                // Otherwise, just chop the end off
                $string = substr($string, 0, $length - $len_append) . $append;
            }
        }
     
        return $string;
    }
}
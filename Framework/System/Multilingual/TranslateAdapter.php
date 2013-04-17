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

namespace Framework\System\Multilingual;

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
abstract class TranslateAdapter
{
    protected $scope = null;

    protected $nplurals;

    protected $pluralExpression;

    protected $pluralFunction;

    abstract function translate($string, $pluralString = null, $count = null);

    public function __construct(Domain $scope)
    {
        $this->scope = $scope;
    }

    public function pluralIndex($count)
    {
        if (!$this->pluralFunction) {
            if (STAGE == TESTING_STAGE) {
                echo 'defaultPlural' . "\n";
            }
            return ($count == 1) ? 0 : 1;
        }
        if (STAGE == TESTING_STAGE) {
            echo 'pluralFunction(' . $this->pluralExpression . ')' . "\n";
        }
        $func = $this->pluralFunction;
        $index = $func($count);

        return $index;
    }

    /**
     * Makes a function, which will return the right translation index, according to the
     * plural forms header
     *
     * @param string $pluralExpression Expression like "nplurals=3; plural=((n%10==1) && (n%100!=11)) ? 0 : (( (n%10>=2) && (n%10<=4) && (n%100<10 || n%100>=20)) ? 1 : 2 );"
     * @return callable
     */
    public function pluralExpression($pluralExpression = null)
    {
        if (STAGE == TESTING_STAGE) {
            echo 'Set expression ' . $pluralExpression . "\n";
        }
        list($nplurals, $expression) = self::_parsePluralExpression($pluralExpression);
        $this->nplurals = $nplurals;

        $expression = str_replace('n', '$n', $expression);
        $this->pluralExpression = "\$index = (int)($expression); return (\$index < $nplurals)? \$index : $nplurals - 1;";
        $this->pluralFunction = create_function('$n', $this->pluralExpression);
        return $this->pluralFunction;
    }

    private static function _parsePluralExpression($header)
    {
        if (preg_match('/^\s*nplurals\s*=\s*(\d+)\s*;\s+plural\s*=\s*(.+)$/', $header, $matches)) {
            $nplurals = (int)$matches[1];
            $expression = $matches[2];
            $expression = self::_pluralExression($expression);
            $expression = trim($expression);
            return array($nplurals, $expression);
        } else {
            return array(2, 'n != 1');
        }
    }

    /**
     * Adds parantheses to the inner parts of ternary operators in
     * plural expressions, because PHP evaluates ternary operators from left to right
     *
     * @param string $expression the expression without parentheses
     * @return string the expression with parentheses added
     */
    private static function _pluralExression($expression)
    {
        $expression .= ';';
        $res = '';
        $depth = 0;
        for ($i = 0; $i < strlen($expression); ++$i) {
            $char = $expression[$i];
            switch ($char) {
                case '?':
                    $res .= ' ? (';
                    $depth++;
                    break;
                case ':':
                    $res .= ') : (';
                    break;
                case ';':
                    $res .= str_repeat(')', $depth) . ';';
                    $depth= 0;
                    break;
                default:
                    $res .= $char;
            }
        }
        return rtrim($res, ';');
    }
}
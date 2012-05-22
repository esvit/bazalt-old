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
abstract class DataType_Abstract extends Object
{
    protected $value;

    public function copy()
    {
        $cls = get_class($this);
        return new $cls($this);
    }

    public function toString()
    {
        return $this->value;
    }

    public function __toString()
    {
        return $this->toString();
    }
}
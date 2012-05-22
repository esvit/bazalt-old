<?php
/**
 * DataType_Manager
 *
 * @category   Core
 * @package    BAZALT
 * @subpackage DataType
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    SVN: $Revision: 178 $
 * @link       http://bazalt-cms.com/
 */

/**
 * DataType_Manager
 *
 * @category   Core
 * @package    BAZALT
 * @subpackage DataType
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */
abstract class DataType_Manager extends DataType_Array implements IWebConfig, IEventable
{
    public $eventOnAttributeLoad = Event::EMPTY_EVENT;

    public $eventOnChildLoad = Event::EMPTY_EVENT;

    public function loadWebConfig($node)
    {
        $this->OnAttributeLoad($node->attributes());

        foreach ($node->nodes() as $elem) {
            $this->OnChildLoad($elem);
        }
        return $this;
    }
}
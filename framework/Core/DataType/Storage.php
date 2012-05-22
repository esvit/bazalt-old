<?php
/**
 * DataType_Storage
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
 * DataType_Storage
 *
 * @category   Core
 * @package    BAZALT
 * @subpackage DataType
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */
class DataType_Storage extends DataType_Array implements IEventable
{
    private $_isDirty = false;
    protected $locked = false;

    public $eventBeforeInsert = Event::EMPTY_EVENT;
    public $eventBeforeDelete = Event::EMPTY_EVENT;
    public $eventBeforeClear  = Event::EMPTY_EVENT;

    public $eventOnInsert = Event::EMPTY_EVENT;
    public $eventOnDelete = Event::EMPTY_EVENT;
    public $eventOnClear  = Event::EMPTY_EVENT;

    public $eventOnInit = Event::EMPTY_EVENT;

    public $eventOnChangeDirtyState = Event::EMPTY_EVENT;

    protected function __construct()
    {
        parent::__construct();
        $this->initStorage();
    }

    protected function initStorage()
    {
        $this->OnInit();
    }

    public function isDirty()
    {
        return $this->_isDirty;
    }

    public function makeDirty()
    {
        $this->_isDirty = true;
        $this->OnChangeDirtyState();
    }

    public function clear()
    {
        $this->BeforeClear();
        parent::clear();
        $this->OnClear();
    }

    public function __get($offset)
    {
        return $this->offsetGet($offset);
    }

    public function __isset($offset)
    {
        return $this->offsetExists($offset);
    }

    public function __set($offset, $value)
    {
        $this->offsetSet($offset, $value);
        $this->makeDirty();
    }

    public function __unset($offset)
    {
        $this->offsetUnset($offset);
        $this->makeDirty();
    }
}
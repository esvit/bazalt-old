<?php
/**
 * DataType_Adaptee
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
 * DataType_Adaptee
 *
 * @category   Core
 * @package    Core
 * @subpackage DataType
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */
abstract class Adaptee
{
    /**
     * Адаптер {@link AbstractSessionDriver}
     */
    protected $adapter = null;

    /**
     * Клас адаптера
     */
    protected $adapterClass;

    /**
     * Namespace адаптера
     */
    protected $adapterNamespace;

    /**
     * Параметри адаптера
     */
    protected $adapterOptions;

    /**
     * Повератє драйвер сесії
     *
     * @return IAbstractAdapter
     */    
    public function getAdapter()
    {
        if ($this->adapter == null) {
            if (!empty($this->adapterNamespace)) {
                using($this->adapterNamespace);
            }
            if (empty($this->adapterClass)) {
                throw new Exception('Unknown adapter');
            }
            $this->adapter = new $this->adapterClass($this, $this->adapterOptions);
        }
        return $this->adapter;
    }

    /**
     * Встановлює адаптер
     *
     * @param IAbstractAdapter $driver Адаптер
     *
     * @return void
     */
    public function setAdapter(IAbstractAdapter $adapter)
    {
        $this->adapter = $adapter;
    }
}
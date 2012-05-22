<?php
/**
 * Abstract.php
 *
 * @category   ORM
 * @package    BAZALT
 * @subpackage System
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */

/**
 * ORM_Plugin_Abstract
 * Клас, що описує плагін ORM
 *
 * @category   ORM
 * @package    BAZALT
 * @subpackage System
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */
abstract class ORM_Plugin_Abstract extends Object implements ISingleton
{
    /**
     * All loaded plugins
     *
     * @var array
     */
    protected static $allPlugins = array();

    /**
     * Options of all plugins
     *
     * @var array
     */
    private static $_options = array();

    /**
     * Init plugin
     *
     * @return void
     */
    abstract function init(ORM_Record $model, $options);

    /**
     * Init model fields
     *
     * @param ORM_Record $model   Record
     * @param array      $options Options for plugin
     *
     * @return void
     */
    protected function initFields(ORM_Record $model, $options)
    {
    }

    /**
     * Init model relations
     *
     * @param ORMRecord $model   Record
     * @param array     $options Options for plugin
     *
     * @return void
     */
    protected function initRelations($model, $options)
    {
    }

    /**
     * Init model plugins
     *
     * @param ORMRecord $model   Record
     * @param array     $options Options for plugin
     *
     * @return void
     */
    protected function initPlugins($model, $options)
    {
    }

    /**
     * Constructor
     */
    protected function __construct()
    {
        parent::__construct();
        self::initPlugin($this);
    }

    /**
     * Init plugin
     *
     * @param ORM_Plugin_Abstract &$plugin Plugin
     *
     * @return void
     */
    protected static function initPlugin(&$plugin)
    {
        self::$allPlugins[get_class($plugin)] = &$plugin;
    }

    /**
     * Return plugin by name
     *
     * @param string $name Name of plugin
     *
     * @return ORM_Plugin_Abstract Plugin
     */
    public static function getPlugin($name)
    {
        if (!array_key_exists($name, self::$allPlugins)) {
            return Type::getObjectInstance($name, null, 'ORM_Plugin_Abstract');
        }
        return self::$allPlugins[$name];
    }

    /**
     * Init plugin for model
     *
     * @param ORM_Record $model   Record
     * @param array      $options Options for plugin
     *
     * @return void
     */
    public function initForModel(ORM_Record $model, $options)
    {
        self::$_options[get_class($this)][get_class($model)] = $options;
        
        $this->initFields($model, $options);
        $this->initRelations($model, $options);
        $this->initPlugins($model, $options);
        
        $this->init($model, $options);
    }

    /**
     * Get plugin options
     *
     * @return array Options
     */
    public function getOptions()
    {
        return self::$_options[get_class($this)];
    }
}

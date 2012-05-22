<?php
/**
 * BaseRecord.php
 *
 * @category   ORM
 * @package    BAZALT
 * @subpackage System
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */

/**
 * ORM_BaseRecord
 *
 * @category   ORM
 * @package    BAZALT
 * @subpackage System
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */
abstract class ORM_BaseRecord extends Object implements IteratorAggregate, IEventable
{
    /**
     * Обробляти при виклику __get
     */
    const ON_FIELD_GET = 1;

    /**
     * Обробляти при виклику __set
     */
    const ON_FIELD_SET = 2;

    /**
     * Обробляти при збереженні
     */
    const ON_RECORD_SAVE = 4;

    /**
     * Обробляти, якщо поле встановлене
     */
    const FIELD_IS_SETTED = 4;

    /**
     * Обробляти, якщо поле не встановлене
     */
    const FIELD_NOT_SETTED = 8;

    /**
     * Ініціалізує поля
     *
     * @return void
     */
    abstract protected function initFields();

    /**
     * Ініціалізує звязки
     *
     * @return void
     */
    abstract function initRelations();

    /**
     * Ініціалізує плагіни
     *
     * @codeCoverageIgnore
     * @return void
     */
    public function initPlugins()
    {
    }
    
    /**
     * Ініціалізує плагіни
     *
     * @codeCoverageIgnore
     * @return void
     */
    protected function initIndexes()
    {
    }

    /**
     * Масив усіх таблиць
     *
     * @var string
     */
    protected static $allTables = array();

    /**
     * Масив усіх ключів
     *
     * @var string
     */
    protected static $allKeys = array();

    /**
     * Масив усіх звязків
     *
     * @var string
     */
    protected static $allReferences = array();

    protected static $allModels = array();

    /**
     * Назва таблиці в СУБД, яка буде відповідати цій моделі
     *
     * @var string
     */ 
    protected $tableName;
    
    /**
     * Table engine (InnoDB or MyISAM)
     *
     * @var string
     */ 
    protected $engine;
 
    protected static $indexes = array();

    protected static $plugins = array();

    protected static $connections = array();

    /**
     * Значення, які були завантажені з БД
     *
     * @var array
     */     
    protected $values = array();
    
    /**
     * Значення, які були завантажені з БД
     *
     * @var array
     */     
    protected $setted = array();

    /**
     * Колонка з автоінкрементом
     *
     * @var ORMColumn
     */
    protected $autoIncrementColumn = null;

    protected static $pluginsEvents = array();

    /**
     * Евент OnSet
     *
     * @var Event
     */
    public $eventOnSet = Event::EMPTY_EVENT;

    /**
     * Евент OnGet
     *
     * @var Event
     */    
    public $eventOnGet = Event::EMPTY_EVENT;    
    
    
    /**
     * Constructor
     *
     * @param string $name Назва таблиці
     * @throws ORM_Exception_Model
     * @codeCoverageIgnore
     */    
    public function __construct($name, $modelName, $engine = null)
    {
        if (!$name) {
            throw new ORM_Exception_Model('Table name cannot be empty', $this);
        }
        if (!$modelName) {
            throw new ORM_Exception_Model('Model name cant be empty "' . $name . '"', $this);
        }
        $this->engine = $engine;

        $this->tableName = $name;

        $this->initModel($name, $modelName);
    }

    public static function registerEvent($model, $type = self::ON_FIELD_GET, $callback, $condition = null)
    {
        if ($condition === null) {
            $condition = self::FIELD_IS_SETTED | self::FIELD_NOT_SETTED;
        }
        if ($type & self::ON_FIELD_GET) {
            self::$pluginsEvents[$model][self::ON_FIELD_GET][] = array(
                'callback'  => $callback,
                'condition' => $condition
            );
        }
        if ($type & self::ON_FIELD_SET) {
            self::$pluginsEvents[$model][self::ON_FIELD_SET][] = array(
                'callback'  => $callback,
                'condition' => $condition
            );
        }
        if ($type & self::ON_RECORD_SAVE) {
            self::$pluginsEvents[$model][self::ON_RECORD_SAVE][] = array(
                'callback'  => $callback,
                'condition' => $condition
            );
        }
    }

    public function callEvent($event, $params = array(), $checkConditions = true)
    {
        $return = false;
        if (!isset(self::$pluginsEvents[get_class($this)]) || !isset(self::$pluginsEvents[get_class($this)][$event])) {
            return $return;
        }
        if (!$checkConditions ||
           (($event['condition'] & self::FIELD_IS_SETTED  &&  isset($this->setted[$name])) ||
            ($event['condition'] & self::FIELD_NOT_SETTED && !isset($this->setted[$name])))) {

            foreach (self::$pluginsEvents[get_class($this)][$event] as $event) {
                call_user_func_array($event['callback'], $params);
            }
        }
        return $return;
    }

    private function initModel($name, $modelName)
    {
        if (!array_key_exists($name, self::$allTables)) {
            self::$allTables[$name] = array();
            self::$allReferences[$name] = array();
            self::$allKeys[$name] = array();
            self::$allModels[$modelName] = $name;

            self::$connections[$modelName] = $this->getSQLConnectionName();

            //Init fields at first request
            $this->initFields();

            //Init relations at first request
            $this->initRelations();

            //Init indexes
            // $this->initIndexes();
            
            //Init plugins
            $this->initPlugins();
            $this->initModelPlugins();
        }
    }

    public function __sleep()
    {
        return array('tableName', 'values', 'setted');
    }

    public function __wakeup()
    {
        $this->initModel($this->tableName, get_class($this));
    }

    public function getSQLConnectionName()
    {
        return ORM_Connection_Manager::DEFAULT_CONNECTION_NAME;
    }

    public static function getSQLConnectionNameByModel($modelName)
    {
        if (!isset(self::$connections[$modelName])) {
            return null;
        }
        return self::$connections[$modelName];
    }

    public function getSettedFields()
    {
        return $this->setted;
    }

    public function getFieldsValues()
    {
        return $this->values;
    }

    public function getField($field)
    {
        if (!isset($this->values[$field])) {
            return null;
        }
        return $this->values[$field];
    }

    public function setField($field, $value)
    {
        $this->setted[$field] = true;
        $this->values[$field] = $value;
    }

    /**
     * Повертає масив стовпців ORMColumn
     *
     * @return array
     */
    public function &getColumns()
    {
        if (!array_key_exists($this->tableName, self::$allTables)) {
            //return array();
            throw new Exception('Table "' . $this->tableName . '" not found');
        }
        return self::$allTables[$this->tableName];
    }

    /**
     * Повертає масив стовпців звязків ORMRelation
     *
     * @return array
     */
    public function &getReferences()
    {
        if (!array_key_exists($this->tableName, self::$allReferences)) {
            //return array();
            throw new Exception('Table "' . $this->tableName . '" not found ');
        }
        return self::$allReferences[$this->tableName];
    }

    /**
     * Повертає масив усіх звязків
     *
     * @return array
     */  
    public static function getAllReferences()
    {
        return self::$allReferences;
    }
    
    /**
     * Повертає масив усіх звязків
     *
     * @return array
     */  
    public static function getByPlugin($plugin)
    {
        $models = array();
        foreach (array_keys(self::$plugins) as $modelName) {
            if (array_key_exists($plugin, self::$plugins[$modelName])) {
                $models []= $modelName;
            }
        }
        return $models;
    }
    
    public function getPlugins()
    {
        $modelName = get_class($this);
        if (!isset(self::$plugins[$modelName])) {
            return null;
        }
        return self::$plugins[$modelName];
    }

    public function getIndexes()
    {
        if (!isset(self::$indexes[$this->tableName])) {
            return null;
        }
        return self::$indexes[$this->tableName];
    }

    /**
     * Повертає об'єкт моделі
     *
     * @param string $className = null Назва моделі
     *
     * @return ORMRecord
     */
    public static function getTable($className)
    {
        if (!class_exists($className)) {
            return null;
        }
        return new $className();
    }

    /**
     * Повертає назву таблиці моделі
     *
     * @param string $className = null Назва моделі
     *
     * @return string
     */
    public static function getTableName($className)
    {
        if (!isset(self::$allModels[$className])) {
            $table = self::getTable($className);
            if ($table == null) {
                return null;
            }
        }
        return self::$allModels[$className];
    }

    /**
     * Повертає масив стовпців моделі - об'єктів ORMColumn {@link ORMColumn}
     *
     * @param string $tableName Назва таблиці моделі
     *
     * @return array
     */ 
    public static function getAllColumns($tableName)
    {
        if (!array_key_exists($tableName, self::$allTables)) {
            throw new ORM_Exception_Table('Table not found', $tableName);
        }
        return self::$allTables[$tableName];
    }

    /**
     * Повертає масив первичних ключів моделі - об'єктів ORMColumn {@link ORMColumn}
     *
     * @param string $tableName Назва таблиці моделі
     *
     * @return array
     */ 
    public static function getPrimaryKeys($tableName)
    {
        $tableName = self::getTableName($tableName);
        return self::$allKeys[$tableName];
    }

    /**
     * Повертає об'єкт автоінкрементного стовпця моделі
     *
     * @param string $tableName Назва таблиці моделі
     *
     * @return ORMColumn
     */     
    public static function getAutoIncrementColumn($tableName)
    {
        $table = self::getTable($tableName);
        if ($table == null) {
            throw new ORM_Exception_Table('Table not found', $tableName);
        }
        $columns = $table->getColumns();
        foreach ($columns as $column) {
            if ($column->isAutoIncrement()) {
                return $column;
            }
        }
    }
    
    /**
     * Повертає значення автоінкрементного стовпця моделі
     *
     * @return mixed
     */     
    public function getAutoIncrementValue()
    {
        foreach ($this->getColumns() as $column) {
            if ($column->isAutoIncrement()) {
                return (int) $this->{$column->name()};
            }
        }
        throw new ORM_Exception_Table('Table does not have any autoincrement columns', $this->tableName);
    }
    
    /**
     * Встановлює $value в поле $name
     *
     * @param string $name  Назва поля
     * @param mixed  $value Значення
     *
     * @return void
     */
    public function __set($name, $value)
    {
        $return = false;
        if (isset(self::$pluginsEvents[get_class($this)]) && isset(self::$pluginsEvents[get_class($this)][self::ON_FIELD_SET])) {
            foreach (self::$pluginsEvents[get_class($this)][self::ON_FIELD_SET] as $event) {
                $callback = $event['callback'];
                if (($event['condition'] & self::FIELD_IS_SETTED  &&  isset($this->setted[$name])) ||
                    ($event['condition'] & self::FIELD_NOT_SETTED && !isset($this->setted[$name]))) {

                    $params = array($this, $name, $value);
                    $params [] = &$return;
                    call_user_func_array($callback, $params);
                }
            }
        }
        //$this->OnSet($this, $name, $value, &$return);
        if ($return !== false) {
            return;
        }

        $references = $this->getReferences();
        if ($references != null && array_key_exists($name, $references)) {
            $relation = clone $references[$name];
            $relation->setBaseObject($this);
            
            $relation->set($value);
            return;
        }

        $this->setField($name, $value);
    }

    /**
     * Повертає значення поля $name
     *
     * @param string $name Назва поля
     *
     * @return mixed
     */
    public function __get($name)
    {
        $return = false;
        if (isset(self::$pluginsEvents[get_class($this)]) && isset(self::$pluginsEvents[get_class($this)][self::ON_FIELD_GET])) {
            foreach (self::$pluginsEvents[get_class($this)][self::ON_FIELD_GET] as $event) {
                $callback = $event['callback'];
                if (($event['condition'] & self::FIELD_IS_SETTED  &&  isset($this->setted[$name])) ||
                    ($event['condition'] & self::FIELD_NOT_SETTED && !isset($this->setted[$name]))) {

                    $params = array($this, $name);
                    $params [] = &$return;
                    call_user_func_array($callback, $params);
                }
            }
        }
        //$this->OnGet($this, $name, &$return);
        if ($return !== false) {
            return $return;
        }

        if (array_key_exists($name, $this->values) && (isset($this->setted[$name]) && $this->setted[$name])) {
            return $this->values[$name];
        } else if (array_key_exists($name, $references = $this->getReferences())) {
            $relation = clone $references[$name];
            $relation->setBaseObject($this);
            
            return ($relation->isManyResult()) ? $relation : $relation->get();
        } else if (array_key_exists($name, $columns = $this->getColumns())) {
            // Якщо значення поля не задано, то встановити по замовчуванню
            if (!array_key_exists($name, $this->values)) {
                $column = $columns[$name];
                if ($column->hasDefault()) {
                    $this->values[$name] = $column->getDefault();
                    $this->setted[$name] = 1;
                } else {
                    return null;
                }
            }
            return $this->values[$name];
        }
        return null;
    }

    /**
     * Додає стовпчик в загальну модель
     *
     * @param string $name    ім'я стовпчика
     * @param string $options опції
     *
     * @return void
     */
    public function hasColumn($name, $options = null)
    {
        $columns = &self::$allTables[$this->tableName];
        if (!array_key_exists($name, $columns)) {
            $column = new ORM_Column($name, $options);
            if ($column->isAutoIncrement()) {
                if ($this->autoIncrementColumn != null) {
                    throw new ORM_Exception_Table('Table cannot have two autoincrement columns', $this->tableName);
                }
                $this->autoIncrementColumn = &$column;
            }
            $columns[$name] = $column;
            if ($column->isPrimaryKey()) {
                self::$allKeys[$this->tableName][$name] = &$column;
            }
        } else {
            return false;
            //throw new ORM_Exception_Table('Column "' . $name . '" already present in this model', $this->tableName);
        }
        return true;
    }

    public function removeColumn($name)
    {
        $columns = &self::$allTables[$this->tableName];

        if (array_key_exists($name, $columns)) {
            unset($columns[$name]);
        }
        if (array_key_exists($name, self::$allKeys[$this->tableName])) {
            unset(self::$allKeys[$this->tableName][$name]);
        }
    }

    /**
     * Додає звязок в модель
     *
     * @param string      $name     ім'я звязка
     * @param ORMRelation $relation звязок
     *
     * @return void
     */
    public function hasRelation($name, ORM_Relation_Abstract $relation)
    {
        $references = &$this->getReferences();
        if (!array_key_exists($name, $references)) {
            $references[$name] = $relation;
            $relation->initForModel($this);
        } else {
            throw new ORM_Exception_Table('Relation "' . $name . '" already present in this model', $this->tableName);
        }
    }

    /**
     * Додає плагін в модель
     *
     * @param string $name    Ім'я плагіна
     * @param array  $options Масив опцій
     *
     * @return void
     */    
    public function hasPlugin($name, $options = array())
    {
        self::$plugins[get_class($this)][$name] = $options;
    }
    
    /**
     * Додає плагін в модель
     *
     * @param string $name    Ім'я плагіна
     * @param array  $options Масив опцій
     *
     * @return void
     */    
    public function hasIndex(ORMIndex $index)
    {
        self::$indexes[$this->tableName][$index->name()] = $index;
    }

    /**
     * Ініціалізує плагіни моделі
     *
     * @return void
     */
    protected function initModelPlugins()
    {
        if (array_key_exists(get_class($this), self::$plugins)) {
            foreach (self::$plugins[get_class($this)] as $name => $options) {
                $plugin = ORM_Plugin_Abstract::getPlugin($name);
                $plugin->initForModel($this, $options);
            }
        }
    }

    /**
     * Create an iterator because private/protected vars can't be seen by json_encode
     *
     * @return ArrayIterator
     */
    public function getIterator()
    {
        $arrResult = array();
        foreach ($this->values as $k => $i) {
            $arrResult[$k] = $i;
        }
        return new ArrayIterator($arrResult);
    }

    /**
     * Перевіряє чи існує поле $name в об'єкті і чи було воно встановлене через __set
     *
     * @param string $name Назва поля
     *
     * @return bool
     */
    public function __isset($name)
    {
        return array_key_exists($name, $this->values) && (isset($this->setted[$name]) && ($this->setted[$name] == true));
    }
    
    /**
     * Перевіряє чи існує стовпець $name в моделі
     *
     * @param string $name Назва поля
     *
     * @return bool
     */
    public function exists($name)
    {
        return array_key_exists($name, $this->getColumns());
    }
    
    /**
     * Видаляє поле $name з об'єкта
     *
     * @param string $name Назва поля
     *
     * @return void
     */
    public function __unset($name)
    {
        unset($this->values[$name]);
        unset($this->setted[$name]);
    }

    /**
     * @ps special for twig
     */
    public function __call($name, $args = array())
    {
        if (count($args) == 0) {
            $trace = debug_backtrace(false);
            foreach ($trace as $step) {
                if (isset($step['class']) && $step['class'] == 'Twig_Template') {
                    return $this->__get($name);
                }
            }
        }
        return parent::__call($name, $args);
    }

    /**
     * Повертає обєкт у вигляді масиву
     *
     * @return array
     */
    public function toArray()
    {
        $res = array();

        foreach ($this->getColumns() as $column) {
            $fieldName = $column->name();
            $res[$fieldName] = $this->$fieldName;
        }
        return $res;
    }

    /**
     * Заповнює обєкт з масиву
     *
     * @param array $data Масив даних виду array( 'назва стовпця' => 'значення' )
     *
     * @return void
     */     
    public function fromArray($data)
    {
        $this->setted = array();
        $this->values = array();
        foreach ($this->getColumns() as $columnName => $column) {
            $this->values[$columnName] = null;
        }
        if (is_array($data)) {
            foreach ($data as $column => $val) {
                $this->values[$column] = $val;
                $this->setted[$column] = true;
            }
        }
    }
}
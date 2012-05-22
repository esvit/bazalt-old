<?php
/**
 * Record.php
 *
 * @category   ORM
 * @package    BAZALT
 * @subpackage System
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */

/**
 * ORM_Record
 * Реалізація патерну Active record pattern {@link http://en.wikipedia.org/wiki/Active_record_pattern}
 *
 * @category   ORM
 * @package    BAZALT
 * @subpackage System
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */ 
abstract class ORM_Record extends ORM_BaseRecord
{
    /**
     * Евент OnSave
     *
     * @var Event
     */
    public $eventOnSave = Event::EMPTY_EVENT;

    /**
     * Евент OnCreate
     *
     * @var Event
     */
    public $eventOnCreate = Event::EMPTY_EVENT;

    /**
     * Евент OnDelete
     *
     * @var Event
     */
    public $eventOnDelete = Event::EMPTY_EVENT;

    /**
     * Повертає обєкт з БД по первинному ключу ( ід )
     *
     * @param integer $id    Значення первинного ключа моделі
     * @param string  $class = null Назва моделі
     *
     * @return ORM_Record
     */
    public static function getRecordById($id, $class = null)
    {
        if (!is_numeric($id)) {
            throw new InvalidArgumentException();
        }

        $className = is_null($class) ? getCalledClass() : $class;

        $field = self::getAutoIncrementColumn($className);
        $q = ORM::select($className . ' f')
                ->andWhere('f.'.$field->name().' = ?', $id)
                ->limit(1);

        return $q->fetch($className);
    }
    
    /**
     * Повертає всі обєкти з БД
     *
     * @param integer|null $limit Ліміт
     * @param string       $class Назва моделі
     *
     * @return array
     */
    public static function getAllRecords($limit = null, $class = null)
    {
        $className = is_null($class) ? getCalledClass() : $class;

        $q = ORM::select($className . ' f');
        if (!is_null($limit)) {
            $q->limit($limit);
        }

        return $q->fetchAll($className);
    }

    /**
     * Оновлює або створює новий запис в БД
     *
     * @return void
     */
    public function save()
    {
        $return = false;
        if (isset(self::$pluginsEvents[get_class($this)]) && isset(self::$pluginsEvents[get_class($this)][self::ON_RECORD_SAVE])) {
            foreach (self::$pluginsEvents[get_class($this)][self::ON_RECORD_SAVE] as $event) {
                $callback = $event['callback'];
                
                $params = array($this);
                $params [] = &$return;
                call_user_func_array($callback, $params);
            }
        }
        //$this->OnSave($this, $return);
        if ($return) {
            return;
        }
    
        $className = get_class($this);
        $column = self::getAutoIncrementColumn($className);

        $res = false;
        if (!$this->isPKEmpty()) {
            $pKeys = self::getPrimaryKeys(get_class($this));
            $q = ORM::select($className, 'COUNT(*) AS cnt');
            foreach ($pKeys as $pKeyName => $pKey) {
                $q->andWhere($pKeyName . ' = ?', $this->{$pKeyName});
            }
            $count = $q->fetch('stdClass');
            if ($count && $count->cnt > 0) {
                $res = true;

                $q = ORM::update($className, $this);
                $q->noCache();
                $q->exec();
            }
        }

        if (!$res) {
            $pKeys = self::getPrimaryKeys(get_class($this));
            $q = ORM::insert($className, $this);
            $q->noCache();
            $q->exec();

            if (!is_null($column)) {
                $fieldName = $column->name();

                $id = $q->Connection->getLastInsertId();
                if (empty($this->$fieldName) && $id > 0) {
                    $this->$fieldName = $id;
                }
            }
            
            $this->OnCreate($this);
        }
    }

    public function isPKEmpty()
    {
        $pKeys = self::getPrimaryKeys(get_class($this));

        foreach ($pKeys as $pKeyName => $pKey) {
            if (empty($this->{$pKeyName})) {
                return true;
            }
        }
        return false;
    }

    /**
     * Видаляє запис з БД
     *
     * @param integer|null $id = null Значення первинного ключа моделі
     *
     * @return integer Кількість задіяних рядків
     */
    public function delete($id = null)
    {
        $className = get_class($this);
        
        $field = self::getAutoIncrementColumn($className);

        $builder = ORM::delete($className);

        if (!is_null($field)) {
            $fieldName = $field->name();
            if (!empty($this->$fieldName)) {
                $builder->where($fieldName . ' = ?', $this->$fieldName);
            } elseif (!is_null($id)) {
                $builder->where($fieldName . ' = ?', $id);
            }
        } else {
            //Якщо такого поля немає - перевіряємо первинні ключі моделі
            $pKeys = self::getPrimaryKeys($className);

            if (count($pKeys) == 0) {
                throw new DontDevelopedYetException();
            }

            foreach ($pKeys as $pKeyName => $pKey) {
                $builder->andWhere($pKeyName . ' = ?', $this->$pKeyName);   
            }
        }
        
        $this->OnDelete($this);
        
        return $builder->exec();
    }
}
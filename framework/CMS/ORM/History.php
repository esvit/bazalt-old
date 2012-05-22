<?php
/**
 * History.php
 *
 * @category   ORM
 * @package    BAZALT
 * @subpackage System
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */

/**
 * CMS_ORM_History
 * Зберігає копію полів, заданих в опціях при кожні зміні або видаленні запису з моделі
 *
 * @category   ORM
 * @package    BAZALT
 * @subpackage System
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */
class CMS_ORM_History extends ORM_Plugin_Abstract
{
    const MODEL_SUFIX = 'History';
    
    const EVENT_TYPE_CREATE = 1;
    
    const EVENT_TYPE_UPDATE = 2;
    
    const EVENT_TYPE_DELETE = 3;
    
    
    /**
     * Ініціалізує плагін
     *
     * @param ORM_Record $model   Модель, для якої викликано initFields
     * @param array      $options Масив опцій, передається з базової моделі при ініціалізації плагіна
     *
     * @return void
     */
    public function init(ORM_Record $model, $options)
    {
        Event::register(get_class($model), 'OnSave', array($this, 'onSave'));
        Event::register(get_class($model), 'OnCreate', array($this, 'onCreate'));
        Event::register(get_class($model), 'OnDelete', array($this, 'onDelete'));
    }

    /**
     * Додає додаткові службові поля до моделі.
     * Викликається в момент ініціалізації моделі
     *
     * @param ORM_Record $model   Модель, для якої викликано initFields
     * @param array      $options Масив опцій, передається з базової моделі при ініціалізації плагіна
     *
     * @return void
     */
    protected function initFields(ORM_Record $model, $options)
    {
    }

    /**
     *
     * @param ORM_Record $record  Поточний запис
     * @param bool       &$return Флаг, який зупиняє подальше виконання save()
     *
     * @return void
     */
    public function onSave(ORM_Record $record, &$return)
    {
        if(isset($record->id)) {
            $this->addToHistory($record, self::EVENT_TYPE_UPDATE, CMS_User::getUser());
        }
    }

    /**
     *
     * @param ORM_Record $record  Поточний запис
     *
     * @return void
     */
    public function onCreate(ORM_Record $record)
    {
        $this->addToHistory($record, self::EVENT_TYPE_CREATE, CMS_User::getUser());
    }

    /**
     *
     * @param ORM_Record $record  Поточний запис
     *
     * @return void
     */
    public function onDelete(ORM_Record $record)
    {
        $this->addToHistory($record, self::EVENT_TYPE_DELETE, CMS_User::getUser());
    }
    
    protected function addToHistory(ORM_Record $record, $event, $user)
    {
        $options = $this->getOptions();
        if (!array_key_exists(get_class($record), $options)) {
            return;
        }
        
        $historyRecordClass = get_class($record) . self::MODEL_SUFIX;
        $historyRecord = new $historyRecordClass();
        
        $options = $options[get_class($record)];
        foreach($options as $recordColumn => $historyColumn) {
            $historyRecord->{$historyColumn} = $record->{$recordColumn};
        }
        $historyRecord->event = $event;
        $historyRecord->author = $user->isGuest() ? null : $user->id;
        $historyRecord->created = date('Y-m-d H:i:s');
        $historyRecord->save();
    }
}
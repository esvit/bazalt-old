<?php
/**
 * Timestampable.php
 *
 * @category   ORM
 * @package    BAZALT
 * @subpackage System
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */

/**
 * ORM_Plugin_Timestampable
 * Плагін, який автоматично заповнить поля created та updated в моделі
 *
 * @category   ORM
 * @package    BAZALT
 * @subpackage System
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */
class ORM_Plugin_Timestampable extends ORM_Plugin_Abstract
{
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
        // Event::register(get_class($model), 'OnSave', array($this,'onSave'));
        ORM_BaseRecord::registerEvent(get_class($model), ORM_BaseRecord::ON_RECORD_SAVE, array($this,'onSave'));
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
        Object::extend(get_class($model), __CLASS__);

        $columns = $model->getColumns();
        if(array_key_exists('created', $options) && !array_key_exists($options['created'], $columns)) {
            $model->hasColumn($options['created'], 'N:timestamp');//|CURRENT_TIMESTAMP
        }
        if(array_key_exists('updated', $options) && !array_key_exists($options['updated'], $columns)) {
            $model->hasColumn($options['updated'], 'N:timestamp');
        }
    }

    /**
     * 
     *
     * @param ORM_Record $record  Поточний запис
     * @param bool       &$return Флаг, який зупиняє подальше виконання save()
     *
     * @return void
     */
    public function onSave(ORM_Record $record, &$return)
    {
        $options = $this->getOptions();
        if (!array_key_exists(get_class($record), $options)) {
            return;
        }
        $options = $options[get_class($record)];
        if(array_key_exists('created', $options) && !isset($record->id)) {
            $record->{$options['created']} = date('Y-m-d H:i:s');
        }
        if(array_key_exists('updated', $options)) {
            $record->{$options['updated']} = date('Y-m-d H:i:s');
        }
    }
}
<?php
/**
 * RelationsCounter.php
 *
 * @category   ORM
 * @package    BAZALT
 * @subpackage System
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */

/**
 * ORM_Plugin_RelationsCounter
 * {@link http://wiki.bazalt.org.ua/ORMRelationsCounter}
 *
 * @category   ORM
 * @package    BAZALT
 * @subpackage System
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */
class ORM_Plugin_RelationsCounter extends ORM_Plugin_Abstract
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
        Event::register($model->$options['relation'], 'OnAdd', array($this,'onAdd'));
        Event::register($model->$options['relation'], 'OnRemove', array($this,'onRemove'));
    }
    
    /**
     * Хендлер на евент onGet моделей які юзають плагін.
     * Евент запалюється при виклику __get() для поля і повертає локалізоване значення
     *
     * @param ORM_Record   $record  Поточний запис
     *
     * @return void 
     */
    public function onAdd(ORM_Record $record, ORM_Record $refRecord)
    {
        $options = $this->getOptions();
        if (!array_key_exists(get_class($record), $options)) {
            return;
        }
        $options = $options[get_class($record)];
        if(isset($options['condition']) && is_array($options['condition'])) {
            foreach($options['condition'] as $field => $value) {
                if($refRecord->$field != $value) {
                    return;
                }
            }
        }
        $record->{$options['field']}++;
        $record->save();
    }
    
    /**
     * Хендлер на евент onSet моделей які юзають плагін.
     * Евент запалюється при виклику __set() для поля і встановлює значення
     * у локалізоване поле. Використовується тільки для FIELDS_LOCALIZABLE 
     *
     * @param ORM_Record $record  Поточний запис
     *
     * @return void 
     */
    public function onRemove(ORM_Record $record, ORM_Record $refRecord)
    {
        $options = $this->getOptions();
        if (!array_key_exists(get_class($record), $options)) {
            return;
        }
        $options = $options[get_class($record)];
        if(isset($options['condition']) && is_array($options['condition'])) {
            foreach($options['condition'] as $field => $value) {
                if($refRecord->$field != $value) {
                    return;
                }
            }
        }
        $record->{$options['field']}--;
        if($record->{$options['field']} < 0) {
            $record->{$options['field']} = 0;
        }
        $record->save();
    }
}
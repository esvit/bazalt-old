<?php
/**
 * Serializable.php
 *
 * @category   ORM
 * @package    BAZALT
 * @subpackage System
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */

/**
 * ORM_Plugin_Serializable 
 * Плагін, що надає змогу автоматично серіалізувати поля в базі даних 
 * {@link http://wiki.bazalt.org.ua/ORMSerializable}
 *
 * @category   ORM
 * @package    BAZALT
 * @subpackage System
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */
class ORM_Plugin_Serializable extends ORM_Plugin_Abstract
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
        //Event::register(get_class($model), 'OnGet', array($this,'onGet'));
        //Event::register(get_class($model), 'OnSet', array($this,'onSet'));
        ORM_BaseRecord::registerEvent(get_class($model), ORM_BaseRecord::ON_FIELD_GET, array($this,'onGet'), ORM_BaseRecord::FIELD_IS_SETTED);
        ORM_BaseRecord::registerEvent(get_class($model), ORM_BaseRecord::ON_FIELD_SET, array($this,'onSet'));
    }
    
    /**
     * Хендлер на евент onGet моделей які юзають плагін.
     * Евент запалюється при виклику __get() для поля і повертає десеріалізоване значення
     *
     * @param ORM_Record   $record  Поточний запис
     * @param string       $field   Поле для якого викликається __get()
     * @param bool|string  &$return Результат, який повернеться методом __get()
     *
     * @return void 
     */
    public function onGet(ORM_Record $record, $field, &$return)
    {
        $options = $this->getOptions();
        if (!array_key_exists(get_class($record), $options)) {
            return;
        }

        $options = $options[get_class($record)];
        if (!is_array($options)) {
            $options = array('fields' => explode(',', $options));
        }
        if (in_array($field, $options['fields'])) {
            $return = unserialize($record->getField($field));
        }
    }
    
    /**
     * Хендлер на евент onSet моделей які юзають плагін.
     * Евент запалюється при виклику __set() для поля і встановлює в поле серіалізоване значення
     *
     * @param ORM_Record $record  Поточний запис
     * @param string     $field   Поле для якого викликається __set()
     * @param string     $value   Значення яке передається в __set()
     * @param bool       &$return Флаг, який зупиняє подальше виконання __set()
     *
     * @return void 
     */
    public function onSet(ORM_Record $record, $field, $value, &$return)
    {
        $options = $this->getOptions();
        if (!array_key_exists(get_class($record), $options)) {
            return;
        }

        $options = $options[get_class($record)];
        if (!is_array($options)) {
            $options = array('fields' => explode(',', $options));
        }
        if (in_array($field, $options['fields'])) {
            $record->setField($field, serialize($value));
            $return = true;
        }
    }
}
<?php
/**
 * Localizable.php
 *
 * @category   CMS
 * @package    ORM
 * @subpackage Plugin
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */

namespace Framework\CMS\ORM;

use Framework\CMS as CMS;
use Framework\System\ORM as ORM;
use Framework\Core\Event;

/**
 * Localizable Плагін, що надає змогу локалізувати поля в базі даних
 * @link http://wiki.bazalt.org.ua/CMS_ORM_Localizable
 *
 * @category   CMS
 * @package    ORM
 * @subpackage Plugin
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */
class Localizable extends ORM\Plugin\AbstractPlugin
{
    const TRANSLATION_NOT_COMPLETED = 0;

    const TRANSLATION_ORIGINAL = 1;

    const TRANSLATION_COMPLETED = 2;

    /**
     * Поточна мова
     */
    protected static $language;

    /**
     * Флаг, показує чи витягувати всі записи чи тільки ті, в яких переклад закінчено.E
     */
    protected static $completeFlag = false;

    /**
     * Додає додаткові службові поля відповідно до типу локалізації.
     * Викликається в момент ініціалізації моделі
     *
     * @param ORM\Record $model   Модель, для якої викликано initFields
     * @param array       $fields  Масив опцій, передається з базової моделі при ініціалізації плагіна
     *
     * @return void
     */
    protected function initFields(ORM\Record $model, $fields)
    {
        if (!self::$language) {
            self::$language = CMS\Language::getCurrentLanguage();
        }

        //$model->hasColumn('lang_id', 'P:int(10)');
        //$model->hasColumn('completed', 'U:tinyint(4)|0');
        foreach ($fields as $field) {
            if (!$model->exists($field)) {
                $model->hasColumn($field, '');
            }
        }
    }

    public static function onChangeLanguage($lang)
    {
        if (!($lang instanceOf CMS\Model\Language)) {
            throw new \Exception('Invalid language object');
        }
        self::setLanguage($lang);
    }

    /**
     * Встановлює self::$completeFlag в true
     *
     * @param bool $flag
     * @return void
     */
    public static function setCompleteFlag($flag = true)
    {
        self::$completeFlag = $flag;
    }

    /**
     * Встановлює мову плагіна
     *
     * @param \Framework\CMS\Model\Language $language ISO код (аліас) поточної мови
     *
     * @return void
     */
    public static function setLanguage(CMS\Model\Language $language)
    {
        self::$language = $language;
    }

    public static function getLanguage()
    {
        if (!self::$language) {
            return CMS\Language::getDefaultLanguage();
        }
        return self::$language;
    }

    /**
     * Ініціалізує плагін
     *
     * @param Record $model   Модель, для якої викликано initFields
     * @param array       $options Масив опцій, передається з базової моделі при ініціалізації плагіна
     *
     * @return void
     */
    public function init(ORM\Record $model, $options)
    {
        ORM\BaseRecord::registerEvent(get_class($model), ORM\BaseRecord::ON_FIELD_GET, array($this,'onGet'), ORM\BaseRecord::FIELD_NOT_SETTED);
        ORM\BaseRecord::registerEvent(get_class($model), ORM\BaseRecord::ON_RECORD_SAVE, array($this,'onSave'));

        Event::register('CMS_Language', 'OnChangeLanguage', array($this, 'onChangeLanguage'));
    }

    /**
     * Хендлер на евент onGet моделей які юзають плагін.
     * Евент запалюється при виклику __get() для поля і повертає локалізоване значення
     *
     * @param ORM\Record   $record  Поточний запис
     * @param string      $field   Поле для якого викликається __get()
     * @param bool|string &$return Результат, який повернеться методом __get()
     *
     * @throws \Exception
     * @return void
     */
    public function onGet(ORM\Record $record, $field, &$return)
    {
        $options = $this->getOptions();
        if (!array_key_exists(get_class($record), $options)) {
            return;
        }

        // Якщо поле вже встановлене
        if (array_key_exists($field, $record->getSettedFields())) {
            return;
        }
        $fields = $options[get_class($record)];
        if (is_array($fields) && in_array($field, $fields)) {
            $defaultLang = CMS\Language::getDefaultLanguage();
            if (!self::$language) {
                self::$language = $defaultLang;
            }
            $q = ORM\ORM::select(get_class($record) . 'Locale l')
                     ->where('l.id = ?', $record->id)
                     ->orderBy('l.completed DESC')
                     ->limit(1);

            if (!self::$completeFlag) {
                $localeQuery = clone $q;
                $localeQuery2 = clone $q;

                $q->andWhere('(l.lang_id = ? AND l.completed >= 1)', self::$language->id);
                $res = $q->fetch('stdClass');
                if (!$res) {
                    $localeQuery->andWhere('l.lang_id = ?', $defaultLang->id);

                    $res = $localeQuery->fetch('stdClass');
                    if (!$res) {
                        $res = $localeQuery2->fetch('stdClass');
                    }
                }
            } else {
                $q->orderBy('(l.lang_id = ' . self::$language->id . ') DESC, (l.lang_id = ' . $defaultLang->id . ') DESC');
                $res = $q->fetch('stdClass');
            }
//                    print $q->toSql();
            /*    print_R($res->$field);exit;
            if (!isset($res->$field) || empty($res->$field)) {
                $res->$field = '13';
            }*/

            if ($res) {
                $return = $res->$field;
                $record->completed = $res->completed;
                $record->lang_id = $res->lang_id;
            }
        }
    }

    /**
     * Хендлер на евент onSave моделей які юзають плагін.
     * Евент запалюється при виклику метаду save() для запису і встановлює значення
     * у локалізовані запис
     *
     * @param ORM\Record $record  Поточний запис
     * @param bool      &$return Флаг, який зупиняє подальше виконання save()
     *
     * @return void
     */
    public function onSave(ORM\Record $record, &$return)
    {
        $options = $this->getOptions();
        if (!array_key_exists(get_class($record), $options)) {
            return;
        }
        $fields = $options[get_class($record)];

        $localRecordClass = get_class($record) . 'Locale';
        $localRecord = new $localRecordClass();
        $return = false;
        foreach ($record->getColumns() as $column) {
            $fieldName = $column->name();
            if (in_array($fieldName, $fields) && array_key_exists($fieldName, $record->getSettedFields())) {
                $localRecord->$fieldName = $record->getField($fieldName);
                unset($record->$fieldName);
                $return = true;
            }
        }
        if (!$return) {
            unset($record->lang_id);
            unset($record->completed);
            return;
        }

        $localRecord->lang_id = $record->lang_id;
        $localRecord->completed = $record->completed;

        // якщо мову не встановлено, то зберігає як стандартну або задану через функцію setLanguage
        if (!$localRecord->lang_id) {
            $defaultLang = self::$language;
            if (!$defaultLang) {
                $defaultLang = CMS\Model\Language::getDefaultLanguage();
            }
            $localRecord->lang_id = $defaultLang->id;
            $localRecord->completed = 1;
        }
        unset($record->lang_id);
        unset($record->completed);
        $record->save();

        $localRecord->id = $record->id;
        $localRecord->save();
    }

    public static function getTranslations(ORM\Record $record)
    {
        if (!$record->id) {
            return null;
        }

        $q = ORM\ORM::select(get_class($record) . 'Locale l')
            ->where('l.id = ?', $record->id);
        $locals = $q->fetchAll('stdClass');

        return $locals;
    }

    public function toArray(ORM\Record $record, $itemArray, $fields)
    {
        $tr = self::getTranslations($record);

        foreach ($fields as $field) {
            $itemArray[$field] = [];
            $lastLangAlias = null;
            foreach ($tr as $item) {
                $lastLangAlias = $item->lang_id;
                $itemArray[$field][$lastLangAlias] = $item->{$field};
                if ($item->completed == Localizable::TRANSLATION_ORIGINAL) {
                    $itemArray[$field]['orig'] = $item->lang_id;
                }
            }
            if (!isset($itemArray[$field]['orig'])) {
                $itemArray[$field]['orig'] = $lastLangAlias;
            }
        }
        return $itemArray;
    }
}

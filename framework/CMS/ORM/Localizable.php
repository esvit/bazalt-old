<?php
/**
 * CMS_ORM_Localizable
 *
 * PHP versions 5
 *
 * LICENSE:
 *
 * This library is free software; you can redistribute it
 * and/or modify it under the terms of the GNU Lesser General
 * Public License as published by the Free Software Foundation;
 * either version 2.1 of the License, or (at your option) any
 * later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @category   System
 * @package    ORM
 * @subpackage Plugins
 * @author     Alex Slubsky <aslubsky@gmail.com>
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version    SVN: $Rev: 148 $
 * @link       http://bazalt.org.ua/
 */

/**
 * CMS_ORM_Localizable Плагін, що надає змогу локалізувати поля в базі даних
 * {@link http://wiki.bazalt.org.ua/CMS_ORM_Localizable}
 *
 * @category   System
 * @package    ORM
 * @subpackage Plugins
 * @author     Alex Slubsky <aslubsky@gmail.com>
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version    Release: $Rev: 148 $
 * @link       http://bazalt.org.ua/
 */
class CMS_ORM_Localizable extends ORM_Plugin_Abstract
{
    /**
     * Тип локалізації за допомогою полів @see http://wiki.bazalt.org.ua/CMS_ORM_Localizable
     */
    const FIELDS_LOCALIZABLE = 1;

    /**
     * Тип локалізації за допомогою строк @see http://wiki.bazalt.org.ua/CMS_ORM_Localizable
     */
    const ROWS_LOCALIZABLE = 2;

    /**
     * Поточна мова
     */
    protected static $language;

    /**
     * Флаг, показує чи витягувати всі записи чи тільки ті, в яких переклад закінчено.
     * Використовується тільки для ROWS_LOCALIZABLE
     */
    protected static $completeFlag = false;

    /**
     * Додає додаткові службові поля відповідно до типу локалізації.
     * Викликається в момент ініціалізації моделі
     *
     * @param ORM_Record $model   Модель, для якої викликано initFields
     * @param array     $options Масив опцій, передається з базової моделі при ініціалізації плагіна
     *
     * @return void
     */
    protected function initFields(ORM_Record $model, $options)
    {
        if (!self::$language) {
            self::$language = CMS_Language::getCurrentLanguage();
        }

        Object::extend(get_class($model), __CLASS__);

        switch ($options['type']) {
        case self::FIELDS_LOCALIZABLE:
            $languages = CMS_Language::getLanguages();
            foreach ($options['fields'] as $field) {
                foreach ($languages as $lang) {
                    $model->hasColumn($field . '_' . $lang->alias, '');
                }
            }
            break;
        case self::ROWS_LOCALIZABLE:
            //$model->hasColumn('lang_id', 'P:int(10)');
            //$model->hasColumn('completed', 'U:tinyint(4)|0');
            foreach ($options['fields'] as $field) {
                if (!$model->exists($field)) {
                    $model->hasColumn($field, '');
                }
            }
            break;
        default:
            throw new Exception('Unknown localizable type '.$options['type']);
        }
    }

    public static function onChangeLanguage($lang)
    {
        if (!($lang instanceOf CMS_Model_Language)) {
            throw new Exception('Invalid language object');
        }
        self::setLanguage($lang);
    }

    public function onAddLanguage($record, $lang)
    {
        $models = ORM_BaseRecord::getByPlugin(__CLASS__);
        $allOptions = $this->getOptions();

        foreach($models as $model) {
            $options = $allOptions[$model];
            if ($options['type'] != self::FIELDS_LOCALIZABLE) {
                continue;
            }

            $tableName = ORM_BaseRecord::getTableName($model);
            foreach ($options['fields'] as $field) {
                $q = new ORMQuery('ALTER TABLE `' . $tableName . '` ADD COLUMN `' . $field . '_' . $lang . '` VARCHAR(255) NULL DEFAULT NULL');
                $q->exec();
            }
        }
    }

    public function onRemoveLanguage($record, $lang)
    {
        $allOptions = $this->getOptions();
        $models = ORM_BaseRecord::getByPlugin(__CLASS__);

        foreach($models as $model) {
            $options = $allOptions[$model];
            if ($options['type'] != self::FIELDS_LOCALIZABLE) {
                continue;
            }

            $tableName = ORM_BaseRecord::getTableName($model);
            foreach ($options['fields'] as $field) {
                $q = new ORM_Query('ALTER TABLE `' . $tableName.'` DROP COLUMN `' . $field . '_' . $lang . '`');
                $q->exec();
            }
        }
    }

    /**
     * Встановлює self::$completeFlag в true
     *
     * @return void
     */
    public static function setCompleteFlag($flag = true)
    {
        self::$completeFlag = $flag;
    }

    /**
     * Встановлює мову плагіна
     *
     * @param string $language ISO код (аліас) поточної мови
     *
     * @return void
     */
    public static function setLanguage(CMS_Model_Language $language)
    {
        self::$language = $language;
    }

    public static function getLanguage()
    {
        if (!self::$language) {
            return CMS_Language::getDefaultLanguage();
        }
        return self::$language;
    }

    /**
     * Ініціалізує плагін
     *
     * @param ORM_Record $model   Модель, для якої викликано initFields
     * @param array     $options Масив опцій, передається з базової моделі при ініціалізації плагіна
     *
     * @return void
     */
    public function init(ORM_Record $model, $options)
    {
        ORM_BaseRecord::registerEvent(get_class($model), ORM_BaseRecord::ON_FIELD_GET, array($this,'onGet'), ORM_BaseRecord::FIELD_NOT_SETTED);
        ORM_BaseRecord::registerEvent(get_class($model), ORM_BaseRecord::ON_FIELD_SET, array($this,'onSet'));
        ORM_BaseRecord::registerEvent(get_class($model), ORM_BaseRecord::ON_RECORD_SAVE, array($this,'onSave'));
        //Event::register(get_class($model), 'OnGet', array($this,'onGet'));
        //Event::register(get_class($model), 'OnSet', array($this,'onSet'));
        //Event::register(get_class($model), 'OnSave', array($this,'onSave'));

        Event::register('CMS_Language', 'OnChangeLanguage', array($this, 'onChangeLanguage'));
        Event::register('CMS_Model_Language', 'OnAdd', array($this, 'onAddLanguage'));
        Event::register('CMS_Model_Language', 'OnRemove', array($this, 'onRemoveLanguage'));
    }

    /**
     * Повертає масив локалізованих записів для $record
     *
     * @param ORM_Record $record Поточний запис
     *
     * @return array
     */
    public function getTranslations(ORM_Record $record)
    {
        $options = $this->getOptions();
        $options = $options[get_class($record)];
        if ($options['type'] != self::ROWS_LOCALIZABLE) {
            throw new Exception( __METHOD__.' can be called only for ROWS_LOCALIZABLE localization type');
        }
        if (!$record->id) {
            return null;
        }

        $q = ORM::select(get_class($record) . 'Locale l')
                ->where('l.id = ?', $record->id);
        $locals = $q->fetchAll();

        $res = array();
        foreach ($locals as $local) {
            $o = clone $record;
            foreach ($options['fields'] as $field) {
                $o->setField($field, $local->getField($field));
            }
            $o->completed = $local->completed;
            $o->lang_id = $local->lang_id;
            $res[$local->lang_id] = $o;
        }
        return $res;
    }

    /**
     * Повертає запис локалізований для мови $lang_id
     *
     * @param ORM_Record $record  Поточний запис
     * @param string    $lang_id Ідентифікатор мови, для якої необхідно локалізувати
     *
     * @return ORM_Record
     */
    public function getTranslation(ORM_Record $record, CMS_Model_Language $lang)
    {
        $options = $this->getOptions();
        $options = $options[get_class($record)];

        if ($options['type'] == self::ROWS_LOCALIZABLE) {
            // throw new Exception( __METHOD__.' can be called only for ROWS_LOCALIZABLE localization type');

            if (empty($record->id)) {
                // Якщо новий об'єкт
                $o = clone $record;
                $o->completed = false;
                $o->lang_id = $lang->id;
                return $o;
            }
            $q = ORM::select(get_class($record) . 'Locale l')
                ->where('l.id = ?', $record->id)
                ->andWhere('l.lang_id = ?', $lang->id);

            $locale = $q->fetch();
            $o = clone $record;
            foreach ($options['fields'] as $field) {
                $o->$field = $locale->$field;
            }
            $o->completed = $locale->completed;
            $o->lang_id = $locale->lang_id;
            return $o;
        }

        foreach ($options['fields'] as $field) {
            $f = $field . '_' . $lang->alias;
            $record->setField($field, $record->$f);
        }
        return $record;
    }

    /**
     * Хендлер на евент onGet моделей які юзають плагін.
     * Евент запалюється при виклику __get() для поля і повертає локалізоване значення
     *
     * @param ORM_Record   $record  Поточний запис
     * @param string      $field   Поле для якого викликається __get()
     * @param bool|string &$return Результат, який повернеться методом __get()
     *
     * @return void
     */
    public function onGet(ORM_Record $record, $field, &$return)
    {
        $options = $this->getOptions();
        if (!array_key_exists(get_class($record), $options)) {
            return;
        }

        // Якщо поле вже встановлене
        if (array_key_exists($field, $record->getSettedFields())) {
            return;
        }
        $options = $options[get_class($record)];
        if (is_array($options['fields']) && in_array($field, $options['fields'])) {
            $defaultLang = CMS_Language::getDefaultLanguage();
            if (!self::$language) {
                self::$language = $defaultLang;
            }
            switch ($options['type']) {
                case self::FIELDS_LOCALIZABLE:
                    $f = $field . '_' . self::$language->alias;

                    if (empty($record->$f)) {
                        $f = $field . '_' . $defaultLang->alias;
                    }
                    $return = $record->getField($f);
                    break;
                case self::ROWS_LOCALIZABLE:
                    $q = ORM::select(get_class($record) . 'Locale l')
                         ->where('l.id = ?', $record->id)
                         ->orderBy('l.completed DESC')
                         ->limit(1);

                    if (!self::$completeFlag) {
                        $localeQuery = clone $q;
                        $localeQuery2 = clone $q;

                        $q->andWhere('(l.lang_id = ? AND l.completed = 1)', self::$language->id);
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

                    if ($res) {
                        $return = $res->$field;
                        $record->completed = $res->completed;
                        $record->lang_id = $res->lang_id;
                    }
                    break;
                default:
                    throw new Exception('Unknown localizable type ' . $options['type']);
            }
        }
    }

    /**
     * Хендлер на евент onSet моделей які юзають плагін.
     * Евент запалюється при виклику __set() для поля і встановлює значення
     * у локалізоване поле. Використовується тільки для FIELDS_LOCALIZABLE
     *
     * @param ORM_Record $record  Поточний запис
     * @param string    $field   Поле для якого викликається __set()
     * @param string    $value   Значення яке передається в __set()
     * @param bool      &$return Флаг, який зупиняє подальше виконання __set()
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
        if (in_array($field, $options['fields']) && $options['type'] === self::FIELDS_LOCALIZABLE) {
            $record->{$field . '_' . self::$language->alias} = $value;
            $return = true;
        }
    }

    /**
     * Хендлер на евент onSave моделей які юзають плагін.
     * Евент запалюється при виклику метаду save() для запису і встановлює значення
     * у локалізовані запсис. Використовується тільки для ROWS_LOCALIZABLE
     *
     * @param ORM_Record $record  Поточний запис
     * @param bool      &$return Флаг, який зупиняє подальше виконання save()
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
        if ($options['type'] === self::ROWS_LOCALIZABLE) {
            $localRecordClass = get_class($record) . 'Locale';
            $localRecord = new $localRecordClass();
            $return = false;
            foreach ($record->getColumns() as $column) {
                $fieldName = $column->name();
                if (in_array($fieldName, $options['fields']) && array_key_exists($fieldName, $record->getSettedFields())) {
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
                    $defaultLang = CMS_Model_Language::getDefaultLanguage();
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
    }
}

<?php
/**
 * Data model for table com_ecommerce_fields
 *
 * @category  DataModels
 * @package   DataModel
 * @author    Bazalt CMS (http://bazalt-cms.com/)
 * @version   SVN: $Id$
 */

/**
 * Data model for table "com_ecommerce_fields"
 *
 * @category  DataModels
 * @package   DataModel
 * @author    Bazalt CMS (http://bazalt-cms.com/)
 * @version   Release: $Revision$
 */
class ComEcommerce_Model_Field extends ComEcommerce_Model_Base_Field
{
    const FIELD_TYPE_BOOL       = 1;
    const FIELD_TYPE_STRING     = 2;
    const FIELD_TYPE_NUMBER     = 3;
    const FIELD_TYPE_SET        = 4;
    const FIELD_TYPE_BOOLSET    = 5;
    const FIELD_TYPE_SEPARATOR  = 6;

    public static function create()
    {
        $field = new ComEcommerce_Model_Field();
        return $field;
    }

    public function getTypes()
    {
        return array( 
            self::FIELD_TYPE_STRING     => __('Text', ComEcommerce::getName()),
            self::FIELD_TYPE_NUMBER     => __('Number', ComEcommerce::getName()),
            self::FIELD_TYPE_BOOL       => __('Checkbox (Yes/No)', ComEcommerce::getName()),
            self::FIELD_TYPE_SET        => __('Dropdown list', ComEcommerce::getName()),
            self::FIELD_TYPE_BOOLSET    => __('Checkbox list', ComEcommerce::getName()),
            self::FIELD_TYPE_SEPARATOR  => __('Separator', ComEcommerce::getName())
        );
    }

    public function isMultifield()
    {
        return ($this->type == self::FIELD_TYPE_BOOLSET);
    }

    public function isBool()
    {
        return ($this->type == self::FIELD_TYPE_BOOL);
    }

    public function isSeparator()
    {
        return ($this->type == self::FIELD_TYPE_SEPARATOR);
    }

    public function getValue()
    {
        return $this->value;
    }

    public function toArray()
    {
        $res = parent::toArray();
        $res['titles'] = array();
        //$res['datas'] = array();
        $translations = $this->getTranslations($this);
        foreach(CMS_Language::getLanguages() as $language) {
            $res['titles'][$language->alias] = isset($translations[$language->id]) ? $translations[$language->id]->title : '';
            /*if(isset($translations[$language->id])) {
                $datas = unserialize($translations[$language->id]->data);
                foreach ($datas as $i => $data) {
                    if(!isset($res['datas'])) {
                        $res['datas'][$i] = array($language->alias => $data);
                    } else {
                        $res['datas'][$i][$language->alias] = $data;
                    }
                }
            }*/
        }
        return $res;
    }

    public function saveOrders($ids = array())
    {
        $i = 0;
        foreach ($ids as $id) {
            $field = ComEcommerce_Model_Field::getById($id);
            $field->order = $i++;
            $field->save();
        }
    }

    public function saveLocale($lang, $title)
    {
        $q = ORM::select('ComEcommerce_Model_FieldLocale fl', 'fl.*')
            ->innerJoin('CMS_Model_Language lng', array('id', 'fl.lang_id'))
            ->where('fl.id = ?', $this->id)
            ->andWhere('lng.alias = ?', $lang)
            ->limit(1);
        $locale = $q->fetch();
        if(!$locale) {
            $locale = new ComEcommerce_Model_FieldLocale();
            $locale->id = $this->id;
            $locale->lang_id = CMS_Model_Language::getLanguageByAlias($lang)->id;
        }
        
        $locale->title = $title;
        $locale->completed = true;
        $locale->save();
    }
}

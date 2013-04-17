<?php

namespace Framework\CMS\ORM;

use Framework\CMS as CMS;
use Framework\System\ORM\ORM;

trait LocalizableTrait
{
    /**
     * Повертає масив локалізованих записів для $record
     *
     * @return array
     */
    public function getTranslations()
    {
        return Localizable::getTranslations($this);
    }

    /**
     * Повертає запис локалізований для мови $lang_id
     *
     * @param \Framework\CMS\Model\Language $lang
     * @internal param string $lang_id Ідентифікатор мови, для якої необхідно локалізувати
     *
     * @return \ORM_Record
     */
    public function getTranslation(CMS\Model\Language $lang)
    {
        $record = $this;
        $options = $this->getOptions();
        $fields = $options[get_class($record)];

        if (empty($record->id)) {
            // Якщо новий об'єкт
            $o = clone $record;
            $o->completed = false;
            $o->lang_id = $lang->id;
            return $o;
        }
        $q = \ORM::select(get_class($record) . 'Locale l')
            ->where('l.id = ?', $record->id)
            ->andWhere('l.lang_id = ?', $lang->id);

        $locale = $q->fetch();
        $o = clone $record;
        foreach ($fields as $field) {
            $o->$field = $locale->$field;
        }
        $o->completed = $locale->completed;
        $o->lang_id = $locale->lang_id;
        return $o;
    }
}

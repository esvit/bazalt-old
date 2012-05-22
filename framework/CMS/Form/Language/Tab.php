<?php

using('Framework.System.Html');

class CMS_Form_Language_Tab extends Html_jQuery_Tab
{
    protected $language = null;

    protected $completedCheckbox = null;

    public function initAttributes()
    {
        parent::initAttributes();

        $this->validAttribute('language',  'object', false);
        $this->validAttribute('isDefault',  'boolean', false);
    }

    public function initElement()
    {
        if ($this->isInited) {
            return;
        }
        $this->isInited = true;
        parent::initElement();

        if (!$this->isDefault()) {
            $this->completedCheckbox = $this->addElement('checkbox', 'completed')
                                            ->label(__('Translation completed', 'CMS'))
                                            ->comment(__('Check when your complete translation', 'CMS'));

            foreach ($this->Elements as $el) {
                if ($el->isRequireField()) {
                    $el->removeRequireValidators();
                }
            }
        }
    }

    protected function prependsName()
    {
        return true;
    }

    public function dataBind(ORM_Record $object)
    {
        $this->initElement();
        if (!$this->isDefault()) {
            $this->completedCheckbox->checked($object->completed);
        }
        return parent::dataBind($object);
    }
}
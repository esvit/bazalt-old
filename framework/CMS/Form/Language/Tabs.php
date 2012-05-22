<?php

using('Framework.System.Html');

class CMS_Form_Language_Tabs extends Html_jQuery_Tabs
{
    protected $defaultLanguageTab = null;

    protected $tabs = null;

    public function __construct()
    {
        parent::__construct('languages_tabs');

        $this->template('elements/language/tabs');
    }

    public function initAttributes()
    {
        parent::initAttributes();

        $this->validAttribute('languages', 'array', false);

        $this->languages(CMS_Language::getLanguages());
    }

    public function initElement()
    {
        if ($this->isInited) {
            return;
        }
        $this->isInited = true;
        parent::initElement();

        foreach ($this->languages() as $language) {
            if (!$language->is_default) {
                $tab = clone $this->defaultLanguageTab;
                $tab->name($language->alias)
                    ->active(false)
                    ->isDefault(false);
                $this->addLanguageTab($language, $tab)
                     ->initElement();
            }
        }
    }

    public function addElement($name, $elementName = null, $options = array())
    {
        if (!$this->defaultLanguageTab) {
            foreach ($this->languages() as $language) {
                if ($language->is_default) {
                    $this->defaultLanguageTab = $this->addLanguageTab($language);
                    $this->defaultLanguageTab->name($language->alias)   
                                             ->active(true)
                                             ->isDefault(true);
                    break;
                }
            }
        }
        return $this->defaultLanguageTab->addElement($name, $elementName, $options);
    }

    public function form(Html_Form $form = null)
    {
        if ($form !== null) {
            $form->AfterFormPost->add(array($this, 'afterFormPost'));
        }
        return parent::form($form);
    }

    public function afterFormPost($form)
    {
        $bindObject = $form->DataBindedObject;
        $plugins = $bindObject->getPlugins();

        $values = $this->container->dataSource()->values();

        if (!is_array($plugins) || !array_key_exists('CMS_ORM_Localizable', $plugins)) {
            return;
            //throw new InvalidArgumentException('Binded object does not have the CMS_ORM_Localizable plugin');
        }
        $plugins = $plugins['CMS_ORM_Localizable'];
        if ($plugins['type'] == CMS_ORM_Localizable::ROWS_LOCALIZABLE) {
            foreach ($this->languages() as $lang) {
                $lValues = $values[$lang->alias];
                if (count($lValues) > 0) {
                    $bindObject = $form->DataBindedObject;
                    $bindObject->lang_id = $lang->id;
                    $bindObject->completed = ($lValues['completed'] == 'on');
                    foreach($bindObject->getColumns() as $key => $column) {
                        if (isset($lValues[$key])) {
                            $bindObject->{$key} = $lValues[$key];
                        }
                    }
                    $bindObject->save();
                }
            }
        } else {
            foreach ($this->languages() as $lang) {
                $lValues = $values[$lang->alias];
                CMS_ORM_Localizable::setLanguage($lang);
                foreach($lValues as $column => $value) {
                    $bindObject->$column = $value;
                }
            }
            $bindObject->save();
        }
    }

    public function addLanguageTab(CMS_Model_Language $language, $tab = null)
    {
        if ($tab == null) {
            $tab = new CMS_Form_Language_Tab($language->alias);
        }
        $tab = parent::addElement($tab)
                     ->language($language)
                     ->isDefault($language->is_default == 1)
                     ->title($language->title)
                     ->id($this->OriginalName . '_' . $language->alias);

        $this->tabs[$language->alias] = $tab;
        return $tab;
    }

    public function dataBind(ORM_Record $object)
    {
        $this->initElement();

        //try { // Category root create exception, fix it
            $trans = $object->getTranslations();//CMS_Model_Language::getDefaultLanguage());
        /*} catch (Exception $e) {
        
        }*/

        foreach ($this->tabs as $tab) {
            foreach ($trans as $tr) {
                if ($tr->lang_id == $tab->language()->id) {
                    $tab->dataBind($tr);
                    break;
                }
            }
        }
        parent::dataBind($object);
    }

    public function isRowLocalizable()
    {
        $obj = $this->form->DataBindedObject;
        if (!$obj) {
            return false;
        }
        $plugins = $obj->getPlugins();

        if(!array_key_exists('CMS_ORM_Localizable', $plugins)) {
            return false;
            //throw new InvalidArgumentException('Binded object does not have the CMS_ORM_Localizable plugin');
        }
        $options = $obj->getOptions();
        $options = $options[get_class($obj)];
        
        return ($options['type'] == CMS_ORM_Localizable::ROWS_LOCALIZABLE);
    }
}

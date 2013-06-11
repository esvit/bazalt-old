<?php

class ComEcommerce_Form_Element_FieldsEditor extends Html_FormElement
{
    protected $fields = array();
    
    public function setFields($fields)
    {
        $this->fields = $fields;
    }

    public function __construct($name = null, $attributes = array())
    {
        parent::__construct($name, $attributes);
        
        $this->template('elements/fields-editor');
        $this->view(CMS_Bazalt::getComponent('ComEcommerce')->getView());
    }
    
    public function toString()
    {
        $view = $this->view();
        $view->assign('fields', $this->fields);
        $view->assign('fieldTypes', ComEcommerce_Model_Field::getTypes());
        return parent::toString();
    }
    
    public function save()
    {
        $values = $this->value();
        $ids = array();
        foreach($values as $fieldData) {
            if(!$fieldData['id']) {
                $field = new ComEcommerce_Model_Field();
            } else {
                $field = ComEcommerce_Model_Field::getById((int)$fieldData['id']);
            }
            $field->name = $fieldData['name'];
            $field->type = $fieldData['type'];
            $field->require = (isset($fieldData['require']) && $fieldData['require'] == 'on');
            $field->save();
            foreach($fieldData['titles'] as $lang => $title) {
                $field->saveLocale($lang, $title, $fieldData['datas'][$lang]);
            }
            $ids []= $field->id;
            if (!$this->form->DataBindedObject->Fields->has($field)) {
                $this->form->DataBindedObject->Fields->add($field);
            }
        }
        $this->form->DataBindedObject->Fields->clearByRelations($ids);
    }
}
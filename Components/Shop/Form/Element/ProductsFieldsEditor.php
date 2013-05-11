<?php

class ComEcommerce_Form_Element_ProductsFieldsEditor extends Html_FormElement
{
    protected $product = null;

    public function dataBind($obj)
    {
        $this->product = $obj;
    }

    public function __construct($name = null, $attributes = array())
    {
        parent::__construct($name, $attributes);
        
        $this->template('elements/products-fields-editor');
        $this->view(CMS_Bazalt::getComponent('ComEcommerce')->getView());
        $this->decorator(new Html_Decorator_Empty());
    }

    public function getJavascript($params = array())
    {
        $params['productId'] = $this->form()->DataBindedObject->id;
        if (empty($params['productId'])) {
            $params['productId'] = 0;
        }
        return parent::getJavascript($params);
    }
    
    public function toString()
    {
        $view = $this->view();

        if (!$this->product) {
            Html_Form::addOnFormInit('loadFields();');
        }

        $view->assign('product', $this->product);
        $view->assign('language', CMS_Language::getCurrentLanguage());
        if ($this->product) {
            $view->assign('fields', $this->product->getFields());
        }
        $view->assign('fieldTypes', ComEcommerce_Model_Field::getTypes());
        return parent::toString();
    }
    
    public function save()
    {
        $values = $this->value();

        $notActive = $values['active'];
        //$active = $values['active'];
        //
        unset($values['active']);
        $fields = array();
        foreach ($values as $id => $fieldData) {
            if (strpos($id, '-') !== false) {
                $id = substr($id, 0, strpos($id, '-'));
            }
            if (in_array($id, $notActive)) {
                continue;
            }
            /*if (!in_array($id, $active)) {
                continue;
            }*/
            if (is_numeric($id)) {
                $field = ComEcommerce_Model_Field::getById((int)$id);
                $value = $fieldData;
                if ($field->type == ComEcommerce_Model_Field::FIELD_TYPE_BOOL) {
                    $value = ($value == '1');
                }
            } else {
                $field = $this->saveField($fieldData);
                $value = $fieldData['value'];
            }
            $fields []= array('field' => $field, 'value' => $value);
        }
        $this->form->DataBindedObject->saveFields($fields);
    }

    protected function saveField($fieldData)
    {
        $field = new ComEcommerce_Model_Field();
        $field->name = $fieldData['name'];
        $field->type = $fieldData['type'];
        $field->data = array();
        if (($field->type == ComEcommerce_Model_Field::FIELD_TYPE_SET ||
             $field->type == ComEcommerce_Model_Field::FIELD_TYPE_BOOLSET ) &&
            isset($fieldData['data']) && is_array($fieldData['data'])) {
            $field->data = $fieldData['data'];
        }
        $field->save();
        foreach ($fieldData['titles'] as $lang => $title) {
            $field->saveLocale($lang, $title, 
                (isset($fieldData['data'][$lang]) ? $fieldData['data'][$lang] : array())
            );
        }
        return $field;
    }
    
    public function ajaxGetFields($productTypeId, $productId = null)
    {
        if (!empty($productId)) {
            $p = ComEcommerce_Model_Product::getById($productId);//AndSiteId
            if(!$p) {
                throw new Exception(sprintf('Product with id "%s" not found', $productId));
            }
            $p->type_id = (int)$productTypeId;
            $fields = $p->getFields();
        } else {
            $pt = ComEcommerce_Model_ProductTypes::getById((int)$productTypeId);
            if(!$pt) {
                throw new Exception(sprintf('Product type with id "%s" not found', $productTypeId));
            }
            $fields = $pt->getFields();
        }
        /*if($pt->site_id != CMS_Bazalt::getSiteId()) {
            throw new CMS_Exception_AccessDenied();
        }*/

        $view = $this->view();

        $view->assign('partial', true);
        $view->assign('product', $this->product);
        $view->assign('language', CMS_Language::getCurrentLanguage());
        $view->assign('fields', $fields);
        $view->assign('fieldTypes', ComEcommerce_Model_Field::getTypes());
        return parent::renderElement();
    }
}
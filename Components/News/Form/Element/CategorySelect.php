<?php

class ComNewsChannel_Form_Element_CategorySelect extends Html_FormElement
{
    protected $checkboxes = true;

    protected $categories = array();
    
    public function checkboxes($checkboxes = null)
    {
        if ($checkboxes !== null) {
            $this->checkboxes = $checkboxes;
            return $this;
        }
        return $this->checkboxes;
    }
    
    public function setCategories($categories)
    {
        $this->categories = $categories;
    }

    public function __construct($name = null, $attributes = array())
    {
        parent::__construct($name, $attributes);
     
        $this->view(CMS_Bazalt::getComponent('ComNewsChannel')->getView());
        $this->template('elements/category-select');
        $this->decorator(new Html_Decorator_Empty());
    }
    
    public function toString()
    {
        CMS_Bazalt::getComponent('ComNewsChannel')->addWebservice('ComNewsChannel_Webservice_TreeCategories');

        $this->view()->assign('category', ComNewsChannel_Model_Category::getSiteRootCategory());
        $this->view()->assign('categories', $this->categories);
        $this->view()->assign('languages', CMS_Language::getLanguages());
        return parent::toString();
    }
    
    public function save()
    {
        $ids = array_values($this->value());
        if(!is_array($ids)) {
            $ids = array();
        }
        foreach($ids as $id) {
            $category = ComNewsChannel_Model_Category::getCategoryById((int)$id);
            if (!$this->form->DataBindedObject->Categories->has($category)) {
                $this->form->DataBindedObject->Categories->add($category);
            }
        }
        $this->form->DataBindedObject->categories_count = count($ids);
        $this->form->DataBindedObject->save();
        $this->form->DataBindedObject->Categories->clearRelations($ids);
    }
}
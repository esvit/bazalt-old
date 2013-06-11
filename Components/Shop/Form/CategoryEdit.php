<?php

using('Framework.System.Html');

class ComEcommerce_Form_CategoryEdit extends Html_Form
{
    protected $title = null;

    protected $languageTabs = null;

    public function __construct()
    {
        parent::__construct('category');

        $component = CMS_Bazalt::getComponent('ComEcommerce');
        $this->view($component->View);

        $this->addElement('validationsummary');

        if ($this->languageTabs == null) {
            $this->languageTabs = $this->addElement(new CMS_Form_Language_Tabs());
        }

        $tab = $this->languageTabs;

        $this->title = $tab->addElement('text', 'title')
                           ->label(__('Title', ComEcommerce::getName()))
                           ->addClass('ui-large-input')
                           ->addRuleNonEmpty();

        $this->addElement('imageuploader', 'image')
            ->allowedExtensions(array('gif', 'jpg', 'png', 'rar'))
            ->label(__('Image', ComEcommerce::getName()))
            ->comment(
                __('Allowed file extensions: gif, jpg, png', ComEcommerce::getName()) .
                '<br />' .
                __('File size limit: 5Mb', ComEcommerce::getName())
            )
            ->limit(1);

        $this->addElement('checkbox', 'is_hidden')
             ->label(__('Hidden category', ComEcommerce::getName()));

        $this->addElement('checkbox', 'is_publish')
             ->label(__('Published category', ComEcommerce::getName()));

        $group = $this->addElement('group');

        $group->addElement('button', 'submit')
              ->content(__('Save', ComEcommerce::getName()))
              ->addClass('btn-primary btn-large');

        $group->addElement('button', 'cancel')
              ->content(__('Cancel', ComEcommerce::getName()))
              ->reset();
    }

    public function ajaxLoadInfo($id)
    {
        $category = ComEcommerce_Model_Category::getById((int)$id);
        if (!$category) {
            throw new Exception('Category not found');
        }
        $this->dataSource()
             ->isPostBack(false);
        $this->dataBind($category);

        return $this->toString();
    }

    public function ajaxSave($id, $data)
    {
        parse_str($data, $output);

        $category = ComEcommerce_Model_Category::getById((int)$id);
        if (!$category) {
            throw new Exception('Category not found');
        }

        $this->dataSource()->isPostBack(true);
        $this->dataBind($category);
        $img = $this['image']->value();
        $category->image = current($img);
        if ($this->validate()){
            $this->save();
        }
        //print_r($this['image']->value());
//        print_r($category);exit;
        //$this->value($output);
        return $this->toString();
    }
}
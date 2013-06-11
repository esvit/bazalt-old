<?php

using('Framework.System.Forms');

class ComEcommerce_Form_BrandEdit extends Html_Form
{
    protected $flash = null;

    public function __construct($name = null, $attributes = array())
    {
        parent::__construct('brand', $attributes);

        $this->addElement('validationsummary', 'errors');
        
        $this->flash = $this->addElement('flasher');

        $this->addElement('text', 'title')
            ->label(__('Title', ComEcommerce::getName()))
            ->addClass('ui-large-input input-title')
            ->addRuleNonEmpty();

        $this->addElement('wysiwyg', 'description')
            ->label(__('Description', ComEcommerce::getName()))
            ->addClass('ui-large-input input-title');
            
        $this->addElement(new CMS_Form_Element_Uploader('logo'), 'logo')
            ->label(__('Logo', ComEcommerce::getName()));
        
        $group = $this->addElement('group');

        $group->addElement('button', 'post')
              ->content(__('Save', ComEcommerce::getName()))
              ->addClass('primary');

        $group->addElement('button', 'cancel')
              ->content(__('Cancel', ComEcommerce::getName()))
              ->reset();
    }

    public function save()
    {
        $text = __('Brand successfully saved.', ComEcommerce::getName());
        $this->flash->text($text);
        return parent::save();
    }
}

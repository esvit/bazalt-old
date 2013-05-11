<?php

class ComEcommerce_Form_ImportForm extends Html_Form
{
    protected $file;

    public function __construct($name = null, $attributes = array())
    {
        parent::__construct('importForm', $attributes);

        $this->addElement('validationsummary', 'errors');

        $this->file = $this->addElement('uploader', 'file')
            ->label(__('CSV File', ComEcommerce::getName()));

        $group = $this->addElement('group');

        $group->addElement('button', 'post')
              ->content(__('Import', ComEcommerce::getName()))
              ->addClass('btn btn-primary');
    }

    public function getFile()
    {
        return $this->file->value();
    }
}

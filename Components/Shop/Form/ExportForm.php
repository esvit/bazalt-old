<?php

class ComEcommerce_Form_ExportForm extends Html_Form
{
    public function __construct($name = null, $attributes = array())
    {
        parent::__construct('product', $attributes);

        $this->addElement('validationsummary', 'errors');

        $select = $this->addElement('select', 'fields')
            ->label(__('Title', ComEcommerce::getName()))
            ->multiple(true);

        $select->addOption('Test');

        $group = $this->addElement('group');

        $group->addElement('button', 'post')
              ->content(__('Export', ComEcommerce::getName()))
              ->addClass('primary');
    }
}

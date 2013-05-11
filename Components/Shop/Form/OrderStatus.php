<?php

using('Framework.System.Html');

class ComEcommerce_Form_OrderStatus extends Html_Form
{
    protected $flasher = null;

    public function __construct()
    {
        parent::__construct(strToLower(__CLASS__));

        $this->addElement('validationsummary');

        $this->flasher = $this->addElement('flasher');

        $status = $this->addElement('select', 'status')
                       ->label(__('Status', ComEcommerce::getName()));

        $statuses = ComEcommerce_Model_Order::getStatuses();
        foreach ($statuses as $n => $str) {
            $status->addOption($str, $n);
        }

        $this->addElement('button', 'submit')
             ->submit()
             ->content(__('Change status', ComEcommerce::getName()));
    }

    public function save()
    {
        $text = __('Order status successfully updated.', ComEcommerce::getName());
        $this->flasher->text($text);

        return parent::save();
    }
}
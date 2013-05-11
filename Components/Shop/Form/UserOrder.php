<?php

using('Framework.System.Html');

class ComEcommerce_Form_UserOrder extends Html_Form
{
    protected $flasher = null;

    protected $nameField = null;

    protected $email = null;

    protected $phone = null;

    protected $subject = null;

    protected $body = null;

    public function __construct()
    {
        parent::__construct(strToLower(__CLASS__));

        $this->addElement('validationsummary');

        $this->flasher = $this->addElement('flasher');

        $this->nameField = $this->addElement('text', 'name')
                           ->label(__('Name', ComEcommerce::getName()))
                           ->addClass('ui-input')
                           ->addRuleNonEmpty();

        $this->phone = $this->addElement('text', 'phone')
                            ->label(__('Contact phone', ComEcommerce::getName()))
                            ->addClass('ui-input')
                           ->addRuleNonEmpty();

        $this->addElement('textarea', 'address')
                           ->label(__('Delivery address', ComEcommerce::getName()))
                           ->addClass('ui-input');

        $this->body = $this->addElement('textarea', 'body')
                           ->label(__('Comments', ComEcommerce::getName()))
                           ->addClass('ui-input');

        $this->addElement('button', 'submit')
             ->submit()
             ->content(__('Order', ComEcommerce::getName()));

        //$this->dataBind(ComEcommerce_Model_Message::create());

        /*if ($this->isPostBack()) {
            $this->value($_POST);

            if ($this->validate()) {
                $this->save();
                $this->flasher->text(__('Thank you for your message. We will get in touch with you as soon as possible.', ComEcommerce::getName()));

                /*$emails = CMS_Option::get(ComEcommerce::EMAILS_OPTION, '');

                if (!empty($emails)) {
                    $mail = CMS_Mail::createMail($emails, $this->body->value(), $this->subject->value());
                    $mail->clearReplyTos();
                    $mail->addReplyTo($this->email->value(), $this->nameField->value());
                    $mail->isHTML(false);
                    $mail->send();

                    $this->subject->value('');
                    $this->body->value('');
                    Url::goBack();
                }
                Url::goBack();
            }
        }*/
    }
}
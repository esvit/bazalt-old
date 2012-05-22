<?php

class Admin_Form_Mail extends Admin_Form_BaseSettings
{
    protected $emailName;

    protected $email;

    protected $useSmtp;

    protected $smtpHost;

    protected $smtpUser;

    protected $smtpPassword;

    protected $smtpPort;

    protected $smtpSecurity;

    public function addSettingFormElements()
    {
        $group = $this->addElement('settingsgroup')
                      ->title(__('Site settings', 'Admin_App'));

        $this->emailName = $group->addElement('text', 'name')
                      ->label(__('Name:', 'Admin_App'))
                      ->addClass('ui-large-input')
                      //->comment(__('', 'Admin_App'))
                      ->addRuleNonEmpty();

        $this->email = $group->addElement('text', 'email')
                      ->label(__('Email:', 'Admin_App'))
                      ->addClass('ui-input')
                      ->comment(__('Email sent from the application will be addressed from the following address', 'Admin_App'))
                      ->addRuleNonEmpty()
                      ->addEmailValidator();

        $this->useSmtp = $group->addElement('checkbox', 'useSmtp')
                       ->label(__('Use an SMTP server to send email', 'AdminApp'))
                       ->comment(__('We will attempt to use the local mail server to send email by default', 'AdminApp'));

        // SMTP
        $group = $this->addElement('settingsgroup')
                      ->title(__('SMTP settings', 'Admin_App'))
                      ->addClass('ui-helper-hidden bz-group-smtp');

        $this->smtpHost = $group->addElement('text', 'smtpHost')
                      ->label(__('SMTP Host:', 'Admin_App'))
                      ->addClass('ui-input')
                      ->comment(__('Example: smtp.gmail.com', 'Admin_App'));

        $this->smtpPort = $group->addElement('text', 'smtpPort')
                      ->label(__('SMTP Port:', 'Admin_App'))
                      ->comment(__('Example: for SSL - 465, for TLS - 587', 'Admin_App'));

        $this->smtpSecurity = $group->addElement('optiongroup', 'smtpSecurity')
                      ->label(__('SMTP Security:', 'Admin_App'))
                      //->comment(__('', 'Admin_App'))
                      ->options(
                        array(
                            'none' => __('None', 'Admin_App'),
                            'ssl' => 'SSL',
                            'tls' => 'TLS'
                        )
                      );

        // SMTP Authorization
        $group = $group->addElement('group')
                       ->addClass('ui-helper-hidden bz-group-smtp-auth');

        $this->smtpUser = $group->addElement('text', 'smtpUser')
                      ->label(__('SMTP User:', 'Admin_App'))
                      ->addClass('ui-input');

        $this->smtpPassword = $group->addElement('password', 'smtpPassword')
                      ->label(__('SMTP Password:', 'Admin_App'))
                      ->addClass('ui-input');

        // Testing
        $group = $this->addElement('settingsgroup')
                      ->title(__('Testing', 'Admin_App'));

        $group->addElement('text', 'testEmail')
              ->label(__('Test address:', 'Admin_App'))
              ->addClass('ui-input')
              ->defaultValue(CMS_User::getUser()->email);

        $group->addElement('inputbutton', 'testBtn')
              ->value(__('Send test email', 'Admin_App'));
    }

    public function validate()
    {
        if ($this->useSmtp->value()) {
            $this->smtpHost->addRuleNonEmpty();
            $this->smtpPort->clearValidators();
            $this->smtpSecurity->addRuleNonEmpty();

            if ($this->smtpSecurity->value() != 'none') {
                $this->smtpUser->addRuleNonEmpty();
                $this->smtpPassword->addRuleNonEmpty();
            }
        }
        return parent::validate();
    }

    public function setDefaultValue()
    {
        $this->emailName->value(CMS_Option::get(CMS_Mail::EMAIL_NAME_OPTION));
        $this->email->value(CMS_Option::get(CMS_Mail::EMAIL_OPTION));
        $this->useSmtp->value(CMS_Option::get(CMS_Mail::USE_SMTP_OPTION));

        $this->smtpHost->value(CMS_Option::get(CMS_Mail::SMTP_HOST_OPTION));
        $this->smtpUser->value(CMS_Option::get(CMS_Mail::SMTP_USER_OPTION));
        $this->smtpPassword->value(CMS_Option::get(CMS_Mail::SMTP_PASSWORD_OPTION, '', true));
        $this->smtpPort->value(CMS_Option::get(CMS_Mail::SMTP_PORT_OPTION));
        $this->smtpSecurity->value(CMS_Option::get(CMS_Mail::SMTP_SECURITY_OPTION));
    }

    public function saveSettings()
    {
        CMS_Option::set(CMS_Mail::EMAIL_NAME_OPTION, $this->emailName->value());
        CMS_Option::set(CMS_Mail::EMAIL_OPTION, $this->email->value());
        CMS_Option::set(CMS_Mail::USE_SMTP_OPTION, $this->useSmtp->value());

        CMS_Option::set(CMS_Mail::SMTP_HOST_OPTION, $this->smtpHost->value());
        CMS_Option::set(CMS_Mail::SMTP_USER_OPTION, $this->smtpUser->value());
        CMS_Option::set(CMS_Mail::SMTP_PASSWORD_OPTION, $this->smtpPassword->value(), null, null, true);
        CMS_Option::set(CMS_Mail::SMTP_PORT_OPTION, $this->smtpPort->value());
        CMS_Option::set(CMS_Mail::SMTP_SECURITY_OPTION, $this->smtpSecurity->value());
    }
}

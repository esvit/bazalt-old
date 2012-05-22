<?php

class Admin_Form_Settings extends Admin_Form_BaseSettings
{
    protected $siteTitle;

    protected $siteHost;

    protected $secretKey;

    protected $siteMultilanguage;

    protected $saveUserLanguage;

    protected $adminLanguage;

    protected $allowSearchBot;

    public function addSettingFormElements()
    {
        $group = $this->addElement('settingsgroup')
                      ->title(__('Site settings', 'AdminApp'));

        $this->siteTitle = $group->addElement('text', 'site_title')
                      ->label(__('Site title:', 'AdminApp'))
                      ->addClass('ui-large-input')
                      ->comment(__('Site Title to appear at the top in the administration panel', 'AdminApp'))
                      ->addRuleNonEmpty();

        /*$this->siteHost = $group->addElement('text', 'site_host')
                      ->label(__('Site host:', 'AdminApp'))
                      ->addClass('ui-input')
                      ->comment(__('The main domain which is the site', 'AdminApp'))
                      ->addRuleNonEmpty()
                      ->addValidator('Html_Validator_Domain');*/

        $this->secretKey = $group->addElement(new Admin_Form_Element_SecretKey('secret_key'), 'secret_key')
                      ->label(__('Secret key:', 'AdminApp'))
                      ->comment(__('Used for basic security settings site. If someone found the key, in the security it needs to change', 'AdminApp'));

        // Language
        $group = $this->addElement('settingsgroup')
                      ->title(__('Language settings', 'AdminApp'));

        $this->siteMultilanguage = $group->addElement('checkbox', 'multilanguage')
                       ->label(__('Multilanguage site', 'AdminApp'))
                       ->comment(__('Turn on the "Multilanguage" section, where you can add multiple languages', 'AdminApp'));

        $this->saveUserLanguage = $group->addElement('checkbox', 'save_language')
                       ->label(__('Store the selected user language', 'AdminApp'))
                       ->comment(__('The site will automatically save the language chosen by the user and display the main page in this language', 'AdminApp'));

        $this->adminLanguage = $group->addElement('select', 'admin_language')
                       ->label(__('Language admin panel', 'AdminApp'))
                       ->comment(__('Language used in admin panel by default', 'AdminApp'));

        $this->adminLanguage->addOption('English', 'en');
        $this->adminLanguage->addOption('Русский', 'ru');
        $this->adminLanguage->addOption('Українська', 'uk');

        // Search engine
        $group = $this->addElement('settingsgroup')
                      ->title(__('Search engines', 'AdminApp'));

        $this->allowSearchBot = $group->addElement('checkbox', 'allowsearchbot')
                       ->label(__('Allow indexing', 'AdminApp'))
                       ->comment(__('Allow indexing site search bots', 'AdminApp'));

        // Cache
        /*$group = $this->addElement(new Admin_Form_Element_SettingsGroup(__('Cache', 'AdminApp')));
        $group->addElement(new Html_Element_Label('cache_salt'))
                       ->value(__(  'Cache salt:', 'AdminApp') . ' ' . Cache::Singleton()->getSalt())
                       ->comment(__('Cache salt', 'AdminApp'));
        $group->addButton('clear_cache')
                       ->title(__('Clear cache', 'AdminApp'));

        Html_jQuery_Form::addOnReady('$("#clear_cache").click(function() { alert("Cache cleared"); return false; });');*/
    }

    public function setDefaultValue()
    {
        $this->siteTitle->value(CMS_Option::get(CMS_Bazalt::SITENAME_OPTION));
        //$this->siteHost->value(CMS_Option::get(CMS_Bazalt::SITEHOST_OPTION));

        $this->siteMultilanguage->value(CMS_Option::get(CMS_Bazalt::MULTILANGUAGE_OPTION));
        $this->saveUserLanguage->value(CMS_Option::get(CMS_Bazalt::SAVE_USER_LANGUAGE_OPTION));

        $this->allowSearchBot->value(CMS_Option::get(CMS_Bazalt::ALLOWSEARCHBOT_OPTION, true));

        $this->adminLanguage->value(CMS_Option::get(Admin_App::ADMIN_LANGUAGE_OPTION, 'ru'));
    }

    public function saveSettings()
    {
        CMS_Option::set(CMS_Bazalt::SITENAME_OPTION, $this->siteTitle->value());
        //CMS_Option::set(CMS_Bazalt::SITEHOST_OPTION, $this->siteHost->value());

        CMS_Option::set(CMS_Bazalt::MULTILANGUAGE_OPTION, $this->siteMultilanguage->value());
        CMS_Option::set(CMS_Bazalt::SAVE_USER_LANGUAGE_OPTION, $this->saveUserLanguage->value());

        CMS_Option::set(CMS_Bazalt::ALLOWSEARCHBOT_OPTION, $this->allowSearchBot->value());
        CMS_Option::set(Admin_App::ADMIN_LANGUAGE_OPTION, $this->adminLanguage->value());
    }
}

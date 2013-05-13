<?php

class ComNewsChannel_Form_Settings extends Admin_Form_BaseSettings
{
    protected $topDays = null;

    protected $vkGroup = null;

    protected $subscribedRole = null;

    public function addSettingFormElements()
    {
        $group = $this->addElement('settingsgroup')
                      ->title(__('Top news settings', ComNewsChannel::getName()));

        $this->topDays = $group->addElement('number', 'top_days')
            ->label(__('Number of days in the top list', ComNewsChannel::getName()))
            ->comment(__('Number of days that the news will be in the top list', ComNewsChannel::getName()));

        $this->subscribedRole = $group->addElement('select', 'subscribedRole')
            ->label(__('Role of subscribed user', ComNewsChannel::getName()))
            ->comment(__('Role of user which subscribed on newsletter', ComNewsChannel::getName()));

        $roles = CMS_Model_Role::getSiteRoles(true,false,true);
        $this->subscribedRole->addOption('-','');
        foreach ($roles as $role) {
            $this->subscribedRole->addOption($role->name, $role->id);
        }

        $group = $this->addElement('settingsgroup')
                      ->title(__('Broadcast news', ComNewsChannel::getName()));

        $this->vkGroup = $group->addElement('text', 'vk_group')
                    ->label(__('VK Group', ComNewsChannel::getName()));
    }

    public function setDefaultValue()
    {
        $this->topDays->value(CMS_Option::get(ComNewsChannel::NEWS_TOPDAYS_OPTION, 5));

        $this->vkGroup->value(CMS_Option::get(ComNewsChannel::NEWS_BROADCAST_VK_GROUP_OPTION, ''));

        $this->subscribedRole->value(CMS_Option::get(ComNewsChannel::NEWS_SUBSCRIBED_ROLE_OPTION, ''));
    }

    public function saveSettings()
    {
        CMS_Option::set(ComNewsChannel::NEWS_TOPDAYS_OPTION, $this->topDays->value());

        CMS_Option::set(ComNewsChannel::NEWS_BROADCAST_VK_GROUP_OPTION, $this->vkGroup->value());

        CMS_Option::set(ComNewsChannel::NEWS_SUBSCRIBED_ROLE_OPTION, $this->subscribedRole->value());
    }
}

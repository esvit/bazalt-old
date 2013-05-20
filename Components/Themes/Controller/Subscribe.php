<?php

class ComNewsChannel_Controller_Subscribe extends CMS_Component_Controller
{
    public function defaultAction($id, $code)
    {
        $user = CMS_Model_User::getById($id);

        $roleId = CMS_Option::get(ComNewsChannel::NEWS_SUBSCRIBED_ROLE_OPTION, '');
        if ($roleId) {
            $subscribeRole = CMS_Model_Role::getById($roleId);
        }

        if ($user->is_active) {
            if ($user->getActivationKey() == $code) {
                if ($subscribeRole && !$user->SiteRoles->has($subscribeRole)) {
                    $user->SiteRoles->add($subscribeRole);
                }
                $this->view->assign('msg', __('You added to the subscribers', ComUsers::getName()));
            } else {
                $this->view->assign('msg', __('Wrong auth code', ComUsers::getName()));
            }
        } else {
            if ($user->getRemindKey() == $code) {
                if ($subscribeRole && !$user->SiteRoles->has($subscribeRole)) {
                    $user->SiteRoles->add($subscribeRole);
                }
                $this->view->assign('msg', __('You added to the subscribers', ComUsers::getName()));
            } else {
                $this->view->assign('msg', __('Wrong auth code', ComUsers::getName()));
            }
        }

        $form = new ComNewsChannel_Form_SubscribeCategories();
        if ($form->isPostBack() && $form->validate()) {

        }
        $this->view->assign('form', $form);

        $this->view->assign('user', $user);
        $this->view->display('news/page.subscribe');
    }
}
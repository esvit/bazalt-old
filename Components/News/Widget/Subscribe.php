<?php
class ComNewsChannel_Widget_Subscribe extends CMS_Widget_Component
{
    public function fetch()
    {
        //CMS_Bazalt::getComponent('ComNewsChannel')->addScript('js/subscribe.js');

        $this->view->assign('user', CMS_User::getUser());
        return parent::fetch();
    }

    public function getConfigPage($config)
    {
        $this->view->assign('options', $this->options);
        return $this->view->fetch('widgets/subscribe-setting');
    }

    public function getJavascriptFile()
    {
        return dirname(__FILE__) . '/../assets/js/subscribe.js';
    }

    public function subscribe($mail)
    {
        $email = filter_var(trim($mail), FILTER_VALIDATE_EMAIL);
        if (!is_string($email)) {
            return array(
                'msg' => __('Wrong email', ComNewsChannel::getName()));
        }

        $user = CMS_Model_User::getUserByEmail($email);
        if (!$user) {
            $user = new CMS_Model_User();
            $user->login = $email;
            $user->email = $email;
            $user->save();

            $link = CMS_Mapper::urlFor('ComNewsChannel.SubscribePage', array(
                'id' => $user->id,
                'code' => $user->getRemindKey()
            ), true);
            CMS_Bazalt::getComponent('ComNewsChannel')->OnGuestSubscribe(array(
                'sitehost' => CMS_Bazalt::getSiteHost(),
                'usermail' => $user->email,
                'subscribeCode' => $user->getRemindKey(),
                'subscribeLink' => $link
            ));
            return array('msg' => __('Please confirm your email', ComNewsChannel::getName()));
        } else {
            $roleId = CMS_Option::get(ComNewsChannel::NEWS_SUBSCRIBED_ROLE_OPTION, '');
            if ($roleId) {
                $subscribeRole = CMS_Model_Role::getById($roleId);
            }

            if ($user->SiteRoles->has($subscribeRole)) {
                return array(
                    'msg' => __('You are already subscribed', ComNewsChannel::getName()));
            } else {
                $link = CMS_Mapper::urlFor('ComNewsChannel.SubscribePage', array(
                            'id' => $user->id,
                            'code' => $user->getActivationKey()
                        ), true);

                CMS_Bazalt::getComponent('ComNewsChannel')->OnUserSubscribe(array(
                    'login' => $user->login,
                    'sitehost' => CMS_Bazalt::getSiteHost(),
                    'username' => $user->getName(),
                    'usermail' => $user->email,
                    'subscribeCode' => $user->getActivationKey(),
                    'subscribeLink' => $link
                ));

                return array(
                    'msg' => __('We sent you a letter, please confirm your intentions', ComNewsChannel::getName()));
            }
        }
    }

}
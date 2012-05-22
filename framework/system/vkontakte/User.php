<?php

class VKontakte_User
{
    protected $app;

    protected $userId;

    protected $info;

    public function __construct(VKontakte_Application $app, $userId)
    {
        $this->userId = $userId;
        $this->app = $app;

        $resp = $app->api('getProfiles', array('uids' => $userId, 'fields' => 'nickname,sex,bdate,city,country,photo,photo_medium,photo_big,photo_rec,online,domain,has_mobile,rate'));

        $this->info = $resp[0];
    }

    public function getFullname()
    {
        $name = array($this->info['first_name']);
        if (!empty($this->info['nickname'])) {
            $name []= $this->info['nickname'];
        }
        $name []= $this->info['last_name'];
        return implode(' ', $name);
    }

    public function __get($name)
    {
        if (isset($this->info[$name])) {
            return $this->info[$name];
        }
        return null;
    }

    public function getInfo()
    {
        return $this->info;
    }

    public function isAppUser()
    {
        $resp = $this->app->api('isAppUser', array('uid' => $this->userId));
        return $resp == '1';
    }

    public function getFriends()
    {
        $resp = $this->app->api('friends.get', array('uid' => $this->userId));
        return $resp;
    }

    public function setAppStatus($status)
    {
        $status = mb_substr($status, 0, 32);
        $resp = $this->app->api('secure.saveAppStatus', array('uid' => $this->userId, 'status' => $status));
        return $resp;
    }

    public function sendNotification($message)
    {
        $message = mb_substr($message, 0, 254);
        $resp = $this->app->api('secure.sendNotification', array('uids' => $this->userId, 'message' => $message), true);
        return $resp;
    }

    /**
     * отримує cookie авторизованорго юзера в Vkontakte
     *
     * @return $member
     */
    public static function authOpenAPIMember()
    {
        $vkApi = CMS_Option::get('Vkontakte_id');
        $vkSec = CMS_Option::get('Vkontakte_secret');
        $session = array();
        $member = FALSE;
        $valid_keys = array('expire', 'mid', 'secret', 'sid', 'sig');
        $app_cookie = $_COOKIE['vk_app_'.$vkApi];
        if ($app_cookie) {
            $session_data = explode ('&', $app_cookie, 10);
            foreach ($session_data as $pair) {
                list($key, $value) = explode('=', $pair, 2);
                if (empty($key) || empty($value) || !in_array($key, $valid_keys)) {
                    continue;
                }
                $session[$key] = $value;
            }
            foreach ($valid_keys as $key) {
                if (!isset($session[$key])) return $member;
            }
            ksort($session);

            $sign = '';
            foreach ($session as $key => $value) {
                if ($key != 'sig') {
                    $sign .= ($key.'='.$value);
                }
            }
            $sign .= $vkSec;
            $sign = md5($sign);
            if ($session['sig'] == $sign && $session['expire'] > time()) {
                $member = array(
                    'id' => intval($session['mid']),
                    'secret' => $session['secret'],
                    'sid' => $session['sid']
                );
            }
        }
        return $member;
    }
}
<?php

class VKontakte_Application
{
    const CURRENT_API_VERSION = '3.0';

    protected $apiId;

    protected $appKey;

    protected $appSecret;

    protected $apiUrl = 'http://api.vk.com/api.php';

    protected $viewerId;

    protected $sessionId = null;

    protected $secret = null;

    public function __construct($appKey, $appSecret)
    {
        $this->appKey = $appKey;
        $this->appSecret = $appSecret;
    }

    public function initIFrame()
    {
        $this->apiUrl = $_GET['api_url'];// – это адрес сервиса API, по которому необходимо осуществлять запросы.
        $this->apiId = $_GET['api_id'];//– это id запущенного приложения.
        $user_id = $_GET['user_id'];// – это id пользователя, со страницы которого было запущено приложение. Если приложение запущено не со страницы пользователя, то значение равно 0.
        $this->sessionId = $_GET['sid'];// – id сессии для осуществления запросов к API
        $this->secret = $_GET['secret'];// – Секрет, необходимый для осуществления подписи запросов к API
        $group_id = $_GET['group_id'];// – это id группы, со страницы которой было запущено приложение. Если приложение запущено не со страницы группы, то значение равно 0.
        $this->viewerId = $_GET['viewer_id'];// – это id пользователя, который просматривает приложение.
        $is_app_user = $_GET['is_app_user'];// – если пользователь установил приложение – 1, иначе – 0.
        $viewer_type = $_GET['viewer_type'];// – это тип пользователя, который просматривает приложение (возможные значения описаны ниже).
        $auth_key = $_GET['auth_key'];// – это ключ, необходимый для авторизации пользователя на стороннем сервере (см. описание ниже).
        $language = $_GET['language'];// – это id языка пользователя, просматривающего приложение (см. список языков ниже).
        $api_result = $_GET['api_result'];// – это результат первого API-запроса, который выполняется при загрузке приложения (см. описание ниже).
        $api_settings = $_GET['api_settings'];// – битовая маска настроек текущего пользователя в данном приложении (подробнее см. в описании метода getUserSettings).

        $serverAuthKey = md5($this->apiId . '_' . $this->viewerId . '_' . $this->appSecret);

        if ($serverAuthKey != $auth_key) {
            throw new Exception('Application error');
        }
    }

    public function api($method, $params = false, $secure = false)
    {
        if (!$params) {
            $params = array();
        }

        $params['api_id'] = $this->apiId;
        $params['method'] = $method;
        $params['timestamp'] = time();
        $params['format'] = 'json';
        $params['random'] = rand(0,10000);
        $params['v'] = self::CURRENT_API_VERSION;

        ksort($params);
        $sig = '';

        foreach ($params as $k => $v) {
            $sig .= $k . '=' . $v;
        }

        if (!$secure && $this->sessionId != null) {
            $params['sid'] = $this->sessionId;
        }

        ksort($params);
        if ($secure) {
            $params['sig'] = md5($sig . $this->appSecret);
        } else {
            $params['sig'] = md5($this->viewerId . $sig . $this->secret);
        }

        $query = $this->apiUrl . '?' . self::concatParams($params);

        $res = file_get_contents($query);

        $res = json_decode($res, true);
        if (array_key_exists('error', $res)) {
            throw new Exception($res['error']['error_msg'] . ' ' . $query, (int)$res['error']['error_code']);
        }
        return $res['response'];
    }

    protected static function concatParams($params)
    {
        $pice = array();
        foreach ($params as $k => $v) {
            $pice[] = $k . '=' . urlencode($v);
        }
        return implode('&', $pice);
    }

    public function getViewer()
    {
        return $this->getUserProfile($this->viewerId);
    }

    public function getUserProfile($id)
    {
        return new VKontakte_User($this, $id);
    }
}
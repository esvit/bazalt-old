<?php

class Facebook extends Object
{

    public function __construct ()
    {
    }

    /**
     * отримує cookie авторизованорго юзера в Facebook
     *
     * @return $args
     */
    public function get_facebook_cookie($app_id, $application_secret) {
        $args = array();
        parse_str(trim($_COOKIE['fbs_' . $app_id], '\\"'), $args);
        ksort($args);
        $payload = '';
        foreach ($args as $key => $value) {
            if ($key != 'sig') {
                $payload .= $key . '=' . $value;
            }
        }
        if (md5($payload . $application_secret) != $args['sig']) {
            return null;
        }
        return $args;
    }
    
    /**
     * отримує user з cookie
     *
     * @return $user
     */
    public function getUserByCookie($cookie) {
        $user = json_decode(
            Url::getContent('https://graph.facebook.com/me?access_token=' . $cookie['access_token'])
        );
        return $user;
    }

    public static function getUrlLiked($url)
    {
        $query = 'https://api.facebook.com/method/fql.query?format=json&query=';

        $q = 'select like_count, total_count, share_count, click_count from link_stat where url="' . $url . '"';
        
        $query .= urlencode($q);

        $result = Url::getContent($query);
        return json_decode($result);
    }

    public static function isAdmin($appId)
    {
        $cookie = self::get_facebook_cookie(CMSOptions::get('Facebook_id'), CMSOptions::get('Facebook_secret'));
        $applications = self::getDevelopers('1357012950', $cookie['access_token']);

        return in_array($appId, $applications);
    }

    public static function getDevelopers($uid, $token)
    {
        $url = self::getRestApiUrl();
        $params = array();
        $params['access_token'] = $token;
        $params['method'] = 'fql.query';
        $params['query'] = 'SELECT application_id FROM developer WHERE developer_id = ' . $uid;
        $apps = self::fetchUrl($url, $params);

        if (array_key_exists('error_code', $apps)) {
            return array();
        }

        $ids = array();
        foreach ($apps as $app) {
            $ids[] = $app['application_id'];
        }
        return $ids;
    }
     
    private static function getUrl($httphost, $path, $params)
    {
        $url = $httphost;
        if ($path) {
          if ($path[0] === '/') {
            $path = substr($path, 1);
          }
          $url .= $path;
        }
        if ($params) {
          $url .= '?' . http_build_query($params);
        }
        return $url;
    }
  
    public static function getRestApiUrl($params = array())
    {
        return self::getUrl('https://api.facebook.com/', 'restserver.php', $params);
    }

    public static function fetchUrl($url, $params)
    {
        $params['format'] = 'json-strings';
        $ch = curl_init();
        $opts = array(
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_USERAGENT => 'facebook-php-2.0',
            CURLOPT_URL => $url,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_CAINFO => dirname(__FILE__) . '/fb_ca_chain_bundle.crt'
        );
        $opts[CURLOPT_POSTFIELDS] = http_build_query($params, null, '&');
        curl_setopt_array($ch, $opts);

        $result = curl_exec($ch);

        if ($result === false) {
            $e = new Exception(curl_error($ch), curl_errno($ch));
            curl_close($ch);
            throw $e;
        }
        curl_close($ch);
        return json_decode($result, true);
    }

}
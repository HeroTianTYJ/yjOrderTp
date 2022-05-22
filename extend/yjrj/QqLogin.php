<?php

namespace yjrj;

use think\facade\Session;

class QqLogin
{
    private array $config;

    public function __construct($config = [])
    {
        $this->config = $config;
    }

    public function login()
    {
        $state = md5(uniqid(rand(), true));
        Session::set('qq_login_state', $state);
        header('Location:https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id=' .
            $this->config['app_id'] . '&redirect_uri=' . $this->config['redirect_uri'] . '&state=' .
            $state . '&scope=get_user_info');
    }

    public function getOpenid($accessToken = '')
    {
        return json_decode(str_replace(['callback( ', ' );'], '', $this->curlGet(
            'https://graph.qq.com/oauth2.0/me?access_token=' . ($accessToken ?: $this->getAccessToken())
        )))->openid;
    }

    public function getUserInfo()
    {
        $accessToken = $this->getAccessToken();
        $openid = $this->getOpenid($accessToken);
        $userInfo = json_decode($this->curlGet('https://graph.qq.com/user/get_user_info?access_token=' . $accessToken .
            '&openid=' . $openid . '&oauth_consumer_key=' . $this->config['app_id']), true);
        $userInfo['openid'] = $openid;
        return $userInfo;
    }

    private function getAccessToken()
    {
        if ($_GET['state'] != Session::get('qq_login_state')) {
            exit('30001');
        }
        Session::delete('qq_login_state');
        $params = [];
        parse_str($this->curlGet('https://graph.qq.com/oauth2.0/token?grant_type=authorization_code&client_id=' .
            $this->config['app_id'] . '&redirect_uri=' . urlencode($this->config['redirect_uri']) . '&client_secret=' .
            $this->config['app_key'] . '&code=' . $_GET['code']), $params);
        return $params['access_token'];
    }

    private function curlGet($url)
    {
        $curl = curl_init();
        if (stripos($url, 'https://') !== false) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_SSLVERSION, 1);
        }
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $content = curl_exec($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);
        if (intval($info['http_code']) == 200) {
            return $content;
        } else {
            return false;
        }
    }
}

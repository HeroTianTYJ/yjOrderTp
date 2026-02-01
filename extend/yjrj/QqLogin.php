<?php

namespace yjrj;

use think\facade\Session;

class QqLogin
{
    private string $appId;
    private string $appKey;
    private string $redirectUri;
    private string $bridge;

    public function __construct($config = [])
    {
        $this->appId = $config['app_id'] ?? '';
        $this->appKey = $config['app_key'] ?? '';
        $this->redirectUri = $config['redirect_uri'];
        $this->bridge = $config['bridge'] ?? '';
    }

    public function login($state = '')
    {
        $state = $state ?: md5(uniqid(rand(), true));
        Session::set('qq_login_state', $state);
        header('Location:' . ($this->bridge ? $this->bridge . (strstr($this->bridge, '?') ? '&' : '?') . 'callback=' .
                urlencode($this->redirectUri) . '&state=' . $state :
                'https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id=' . $this->appId .
                '&redirect_uri=' . urlencode($this->redirectUri) . '&state=' . $state .
                '&scope=get_user_info'));
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
            '&openid=' . $openid . '&oauth_consumer_key=' . $this->appId), true);
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
            $this->appId . '&redirect_uri=' . urlencode($this->bridge ?: $this->redirectUri) . '&client_secret=' .
            $this->appKey . '&code=' . $_GET['code']), $params);
        return $params['access_token'];
    }

    private function curlGet($url)
    {
        $option = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true
        ];
        if (stripos($url, 'https://') !== false) {
            $option[CURLOPT_SSL_VERIFYPEER] = $option[CURLOPT_SSL_VERIFYHOST] = false;
            $option[CURLOPT_SSLVERSION] = true;
        }
        $curl = curl_init();
        curl_setopt_array($curl, $option);
        $content = curl_exec($curl);
        if (version_compare(PHP_VERSION, '8.5.0', '<')) {
            curl_close($curl);
        }
        return $content;
    }
}

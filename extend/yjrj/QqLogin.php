<?php

namespace yjrj;

use think\facade\Session;

class QqLogin
{
    public string $error = '';

    private string $appId;
    private string $appKey;
    private string $redirectUri;
    private string $bridge;

    private const API_URL_PREFIX = 'https://graph.qq.com/oauth2.0';
    private const USER_URL_PREFIX = 'https://graph.qq.com/user/get_user_info';

    public function __construct($config = [])
    {
        $this->appId = $config['app_id'] ?? '';
        $this->appKey = $config['app_key'] ?? '';
        $this->redirectUri = $config['redirect_uri'];
        $this->bridge = $config['bridge'] ?? '';
    }

    public function login($state = '')
    {
        $state = $state ?: getKey(32);
        Session::set('qq_login_state', $state);
        header('Location:' . ($this->bridge ? $this->bridge . (strstr($this->bridge, '?') ? '&' : '?') . 'callback=' .
                urlencode($this->redirectUri) . '&state=' . $state : self::API_URL_PREFIX .
                '/authorize?response_type=code&client_id=' . $this->appId . '&redirect_uri=' .
                urlencode($this->redirectUri) . '&state=' . $state));
    }

    public function getUserInfo($code = '', $state = '', $authOnly = true)
    {
        $userInfo = [];
        $accessToken = $this->getAccessToken($code, $state);
        if ($accessToken) {
            $data = $this->getData($this->curlGet(self::API_URL_PREFIX . '/me?access_token=' . $accessToken .
                '&unionid=1'));
            if ($data) {
                $userInfo['openid'] = $data['openid'];
                $userInfo['unionid'] = str_replace('UID_', '', $data['unionid']);
                if (!$authOnly) {
                    $data2 = $this->getData($this->curlGet(self::USER_URL_PREFIX . '?oauth_consumer_key=' .
                        $this->appId . '&access_token=' . $accessToken . '&openid=' . $data['openid']));
                    if ($data2) {
                        $userInfo['nickname'] = $data2['nickname'];
                        $userInfo['avatar'] = $data2['figureurl_2'];
                    }
                }
            }
        }
        return $userInfo;
    }

    private function getAccessToken($code = '', $state = '')
    {
        if ($state != Session::get('qq_login_state')) {
            $this->error = 'state error';
            return '';
        }
        Session::delete('qq_login_state');
        $data = $this->curlGet(self::API_URL_PREFIX . '/token?grant_type=authorization_code&client_id=' . $this->appId .
            '&redirect_uri=' . urlencode($this->bridge ?: $this->redirectUri) . '&client_secret=' . $this->appKey .
            '&code=' . $code);
        if (substr($data, 0, 11) == 'callback( {') {
            $this->getData($data);
            return '';
        }
        $params = [];
        parse_str($data, $params);
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

    private function getData($result)
    {
        if ($result) {
            $data = json_decode(str_replace(['callback( ', ' );'], '', $result), true);
            if (isset($data['error_description'])) {
                $this->error = $data['error_description'];
                return '';
            }
            return $data;
        }
        return '';
    }
}

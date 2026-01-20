<?php

namespace yjrj;

use think\facade\Config;

class Wechat
{
    public string $errMsg = 'no access';

    private string $appId;
    private string $appSecret;
    private string $bridge;
    private bool $isMp;

    private const API_URL_PREFIX = 'https://api.weixin.qq.com/cgi-bin';
    private const SNS_URL_PREFIX = 'https://api.weixin.qq.com/sns';
    private const OPEN_URL_PREFIX = 'https://open.weixin.qq.com/connect';

    public function __construct($config)
    {
        $this->appId = $config['app_id'] ?? '';
        $this->appSecret = $config['app_secret'] ?? '';
        $this->bridge = $config['bridge'] ?? '';
        $this->isMp = $config['is_mp'] ?? true;
    }

    public function getShareConfig()
    {
        $config = [
            'app_id' => $this->appId,
            'jsapi_ticket' => $this->getJsTicket2(),
            'nonce_str' => getKey(16),
            'timestamp' => time(),
            'url' => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http') . '://' .
                $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']
        ];
        $config['signature'] = sha1('jsapi_ticket=' . $config['jsapi_ticket'] . '&noncestr=' . $config['nonce_str'] .
            '&timestamp=' . $config['timestamp'] . '&url=' . $config['url']);
        return $config;
    }

    public function oauthRedirect($redirectUrl, $state = '', $scope = 'userinfo')
    {
        header('Location:' . ($this->bridge ? $this->bridge . (strstr($this->bridge, '?') ? '&' : '?') . 'callback=' .
                urlencode($redirectUrl) . '&scope=' . $scope : self::OPEN_URL_PREFIX . ($this->isMp ?
                    '/oauth2/authorize?appid=' . $this->appId . '&redirect_uri=' . urlencode($redirectUrl) .
                    '&response_type=code&scope=snsapi_' . $scope : '/qrconnect?appid=' . $this->appId .
                    '&redirect_uri=' . urlencode($redirectUrl) . '&response_type=code&scope=snsapi_login') . '&state=' .
                $state . '#wechat_redirect'));
        exit;
    }

    public function getUserInfo($code = '', $authOnly = true, $lang = 'zh_CN')
    {
        $result = $this->sendGet(self::SNS_URL_PREFIX . '/oauth2/access_token?appid=' . $this->appId . '&secret=' .
            $this->appSecret . '&code=' . $code . '&grant_type=authorization_code');
        return $authOnly ? $result : $this->sendGet(self::SNS_URL_PREFIX . '/userinfo?access_token=' .
            $result['access_token'] . '&openid=' . $result['openid'] . '&lang=' . $lang);
    }

    public function getAccessToken()
    {
        return $this->sendGet(self::API_URL_PREFIX . '/token?grant_type=client_credential&appid=' . $this->appId .
            '&secret=' . $this->appSecret, 'access_token');
    }

    private function getAccessToken2()
    {
        if ($_SERVER['HTTP_HOST'] == 'www.yjrj.top' || $_SERVER['HTTP_HOST'] == 'www.yjrj.cn') {
            $file = ROOT_DIR . '/../../../common/' . $this->appId . '.php';
            $config = include($file);
            if (isset($config['cache'], $config['token']) && time() - $config['cache'] < 5400 && $config['token']) {
                $accessToken = $config['token'];
            } else {
                $accessToken = $this->getAccessToken();
                file_put_contents($file, "<?php

return [
    'cache' => " . time() . ",
    'token' => '" . $accessToken . "'
];
");
            }
        } else {
            Config::load('cache/' . $this->appId, $this->appId);
            $config = Config::get($this->appId);
            if (isset($config['cache'], $config['token']) && time() - $config['cache'] < 5400 && $config['token']) {
                $accessToken = $config['token'];
            } else {
                $accessToken = $this->getAccessToken();
                file_put_contents(ROOT_DIR . '/config/cache/' . $this->appId . '.php', "<?php

return [
    'cache' => " . time() . ",
    'token' => '" . $accessToken . "'
];
");
            }
        }
        return $accessToken;
    }

    private function getJsTicket()
    {
        return $this->sendGet(self::API_URL_PREFIX . '/ticket/getticket?access_token=' . $this->getAccessToken2() .
            '&type=jsapi', 'ticket');
    }

    private function getJsTicket2()
    {
        if (time() - Config::get('wechat.cache') < 5400 && Config::get('wechat.js_ticket')) {
            $jsTicket = Config::get('wechat.js_ticket');
        } else {
            $jsTicket = $this->getJsTicket();
            file_put_contents(ROOT_DIR . '/app/index/config/wechat.php', "<?php

return [
    'cache' => " . time() . ",
    'js_ticket' => '" . $jsTicket . "'
];

");
        }
        return $jsTicket;
    }

    private function sendGet($url, $returnField = '')
    {
        return $this->getData($this->httpGet($url), $returnField);
    }

    private function httpGet($url)
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
        curl_close($curl);
        return $content;
    }

    private function getData($result, $returnField = false)
    {
        if ($result) {
            $data = json_decode($result, true);
            if (!empty($data['errcode'])) {
                $this->errMsg = $data['errmsg'];
                return false;
            }
            if ($returnField) {
                return $data[$returnField] ?? false;
            }
            return $data;
        }
        return false;
    }
}

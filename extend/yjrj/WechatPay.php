<?php

namespace yjrj;

class WechatPay
{
    private string $appId;
    private string $appSecret;
    private string $mchId;
    private string $key;
    private string $certSerialNumber;
    private string $certPrivateKey;
    private string $bridge;

    private const API_URL_PREFIX = 'https://api.mch.weixin.qq.com/v3/pay/transactions/';

    public function __construct($config = [])
    {
        $this->appId = $config['app_id'] ?? '';
        $this->appSecret = $config['app_secret'] ?? '';
        $this->mchId = $config['mch_id'] ?? '';
        $this->key = $config['key'] ?? '';
        $this->certSerialNumber = $config['cert_serial_number'] ?? '';
        $this->certPrivateKey = $config['cert_private_key'] ?? '';
        $this->bridge = $config['bridge'] ?? '';
    }

    public function native($param = [])
    {
        return $this->pay(self::API_URL_PREFIX . 'native', $param, 'code_url');
    }

    public function jsapi($param = [], $openid = '')
    {
        if (isset($_GET['code']) || $openid) {
            $param['payer']['openid'] = $openid ?: $this->httpPost('https://api.weixin.qq.com/sns/oauth2/access_token' .
                '?appid=' . $this->appId . '&secret=' . $this->appSecret . '&code=' . $_GET['code'] .
                '&grant_type=authorization_code', [], [], 'openid');
            $payParam = [
                'appId' => $this->appId,
                'nonceStr' => $this->getNonceStr(32),
                'package' => 'prepay_id=' . $this->pay(self::API_URL_PREFIX . 'jsapi', $param, 'prepay_id'),
                'timeStamp' => time() . '',
                'signType' => 'RSA'
            ];
            openssl_sign(
                $payParam['appId'] . "\n" . $payParam['timeStamp'] . "\n" . $payParam['nonceStr'] . "\n" .
                $payParam['package'] . "\n",
                $signature,
                openssl_get_privatekey($this->certPrivateKey),
                'sha256WithRSAEncryption'
            );
            $payParam['paySign'] = base64_encode($signature);
            return json_encode($payParam);
        } else {
            $redirectUrl = urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http') .
                '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . $_SERVER['QUERY_STRING']);
            header('Location:' . ($this->bridge ? $this->bridge . '?callback=' . urlencode($redirectUrl) :
                    'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . $this->appId . '&redirect_uri=' .
                    $redirectUrl . '&response_type=code&scope=snsapi_base&state=STATE#wechat_redirect'));
            exit;
        }
    }

    public function h5($param = [])
    {
        $param['scene_info'] = ['payer_client_ip' => $_SERVER['REMOTE_ADDR'], 'h5_info' => ['type' => 'Wap']];
        return $this->pay(self::API_URL_PREFIX . 'h5', $param, 'h5_url');
    }

    public function notify()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if ($data['event_type'] == 'TRANSACTION.SUCCESS') {
            $resource = $data['resource'];
            $ciphertext = base64_decode($resource['ciphertext']);
            $result = json_decode(openssl_decrypt(
                substr($ciphertext, 0, -16),
                'aes-256-gcm',
                $this->key,
                OPENSSL_RAW_DATA,
                $resource['nonce'],
                substr($ciphertext, -16),
                $resource['associated_data']
            ), true);
            if (
                $result['appid'] == $this->appId && $result['mchid'] == $this->mchId &&
                $result['trade_state'] == 'SUCCESS'
            ) {
                $result['success_time'] = strtotime($result['success_time']);
                return $result;
            }
        }
        return false;
    }

    private function pay($url = '', $param = [], $field = '')
    {
        $param['appid'] = $this->appId;
        $param['mchid'] = $this->mchId;
        $param['amount'] = ['total' => $param['total'], 'currency' => 'CNY'];
        unset($param['total']);

        $time = time();
        $nonce = $this->getNonceStr(32);
        $parseUrl = parse_url($url);
        openssl_sign(
            "POST\n" . ($parseUrl['path'] . (!empty($parseUrl['query']) ? '?' . $parseUrl['query'] : '')) . "\n" .
                $time . "\n" . $nonce . "\n" . json_encode($param) . "\n",
            $signature,
            openssl_get_privatekey($this->certPrivateKey),
            'sha256WithRSAEncryption'
        );

        return $this->httpPost($url, $param, [
            'Accept:application/json',
            'User-Agent:*/*',
            'Content-Type:application/json;charset=utf-8',
            'Authorization:WECHATPAY2-SHA256-RSA2048 mchid="' . $this->mchId . '",nonce_str="' . $nonce .
                '",timestamp="' . $time . '",serial_no="' . $this->certSerialNumber . '",signature="' .
                base64_encode($signature) . '"'
            ], $field);
    }

    private function getNonceStr($length = 10)
    {
        $nonceStr = '';
        $charset = 'abcdefghijklmnopqrstuvwxyz0123456789';
        for ($i = 0; $i < $length; $i++) {
            $nonceStr .= $charset[mt_rand(0, strlen($charset) - 1)];
        }
        return $nonceStr;
    }

    private function httpPost($url, $data = [], $header = [], $field = '')
    {
        $option = [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $header,
            CURLINFO_HEADER_OUT => true
        ];
        if (stripos($url, 'https://') !== false) {
            $option[CURLOPT_SSL_VERIFYPEER] = $option[CURLOPT_SSL_VERIFYHOST] = false;
            $option[CURLOPT_SSLVERSION] = true;
        }
        $curl = curl_init();
        curl_setopt_array($curl, $option);
        $content = json_decode(curl_exec($curl), true);
        curl_close($curl);
        return $content[$field] ?? $content;
    }
}

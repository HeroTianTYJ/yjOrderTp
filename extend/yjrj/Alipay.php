<?php

namespace yjrj;

class Alipay
{
    private string $appId;
    private string $merchantPrivateKey;
    private string $publicKey;
    private string $method;

    public function __construct($config)
    {
        $this->appId = trim($config['app_id'] ?? '');
        $this->merchantPrivateKey = trim($config['merchant_private_key'] ?? '');
        $this->publicKey = trim($config['public_key'] ?? '');
        $this->method = trim($config['method'] ?? '');
    }

    public function pay($product = [], $url = [])
    {
        $product['product_code'] = $this->method == 'page' ? 'FAST_INSTANT_TRADE_PAY' : 'QUICK_WAP_PAY';
        $param = [
            'app_id' => $this->appId,
            'version' => '1.0',
            'format' => 'json',
            'sign_type' => 'RSA2',
            'method' => 'alipay.trade.' . $this->method . '.pay',
            'timestamp' => date('Y-m-d H:i:s'),
            'alipay_sdk' => 'alipay-sdk-php-20161101',
            'return_url' => $url['return'],
            'notify_url' => $url['notify'],
            'charset' => 'UTF-8',
            'biz_content' => json_encode($product, JSON_UNESCAPED_UNICODE)
        ];
        openssl_sign($this->getSignContent($param), $sign, '-----BEGIN RSA PRIVATE KEY-----' . "\n" .
            wordwrap($this->merchantPrivateKey, 64, "\n", true) . "\n" .
            '-----END RSA PRIVATE KEY-----', OPENSSL_ALGO_SHA256);
        $param['sign'] = base64_encode($sign);

        $html = '<form id="alipay_submit" name="alipay_submit" action="https://openapi.alipay.com/gateway.do?charset=' .
            'UTF-8" method="post">';
        foreach ($param as $key => $value) {
            if (trim($value)) {
                $html .= '<input type="hidden" name="' . $key . '" value="' . str_replace('"', '&quot;', $value) . '">';
            }
        }
        $html .= '<input type="submit" value="ok" style="display:none;"></form><script type="text/javascript">' .
            'document.forms["alipay_submit"].submit();</script>';
        return $html;
    }

    public function check($array = [])
    {
        $sign = $array['sign'];
        $array['sign_type'] = $array['sign'] = null;
        return (bool)openssl_verify($this->getSignContent($array), base64_decode($sign), '-----BEGIN PUBLIC KEY-----' .
            "\n" . wordwrap($this->publicKey, 64, "\n", true) . "\n" . '-----END PUBLIC KEY-----', OPENSSL_ALGO_SHA256);
    }

    private function getSignContent($param = [])
    {
        ksort($param);
        $string2Sign = '';
        $i = 0;
        foreach ($param as $key => $value) {
            if ($value && trim($value) && substr($value, 0, 1) != '@') {
                $string2Sign .= ($i == 0 ? '' : '&') . $key . '=' . $value;
                $i++;
            }
        }
        return $string2Sign;
    }
}

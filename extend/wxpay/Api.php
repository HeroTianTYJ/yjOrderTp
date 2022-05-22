<?php

namespace wxpay;

use Exception;

class Api
{
    /**
     * @param UnifiedOrder $inputObj
     * @param int $timeOut
     * @return array
     * @throws Exception
     */
    public static function unifiedOrder(UnifiedOrder $inputObj, int $timeOut = 6)
    {
        if (!$inputObj->isOutTradeNoSet()) {
            throw new Exception('缺少统一支付接口必填参数out_trade_no！');
        }
        if (!$inputObj->isBodySet()) {
            throw new Exception('缺少统一支付接口必填参数body！');
        }
        if (!$inputObj->isTotalFeeSet()) {
            throw new Exception('缺少统一支付接口必填参数total_fee！');
        }
        if (!$inputObj->isTradeTypeSet()) {
            throw new Exception('缺少统一支付接口必填参数trade_type！');
        }
        if ($inputObj->getTradeType() == 'JSAPI' && !$inputObj->isOpenidSet()) {
            throw new Exception('统一支付接口中，缺少必填参数openid！trade_type为JSAPI时，openid为必填参数！');
        }
        if ($inputObj->getTradeType() == 'NATIVE' && !$inputObj->isProductIdSet()) {
            throw new Exception('统一支付接口中，缺少必填参数product_id！trade_type为JSAPI时，product_id为必填参数！');
        }

        $inputObj->setAppid(APPID);
        $inputObj->setMchId(MCHID);
        $inputObj->setSpbillCreateIp($_SERVER['REMOTE_ADDR']);
        $inputObj->setNonceStr(self::getNonceStr());
        $inputObj->setSign();

        return Results::init(self::postXmlCurl(
            $inputObj->toXml(),
            'https://api.mch.weixin.qq.com/pay/unifiedorder',
            $timeOut
        ));
    }

    /**
     * @param OrderQuery $inputObj
     * @param int $timeOut
     * @return array
     * @throws Exception
     */
    public static function orderQuery(OrderQuery $inputObj, int $timeOut = 6)
    {
        if (!$inputObj->isOutTradeNoSet() && !$inputObj->isTransactionIdSet()) {
            throw new Exception('订单查询接口中，out_trade_no、transaction_id至少填一个！');
        }

        $inputObj->setAppid(APPID);
        $inputObj->setMchId(MCHID);
        $inputObj->setNonceStr(self::getNonceStr());
        $inputObj->setSign();

        return Results::init(self::postXmlCurl(
            $inputObj->toXml(),
            'https://api.mch.weixin.qq.com/pay/orderquery',
            $timeOut
        ));
    }

    public static function getNonceStr(int $length = 32)
    {
        $key = '';
        $charset = 'abcdefghijklmnopqrstuvwxyz0123456789';
        for ($i = 0; $i < $length; $i++) {
            $key .= $charset[mt_rand(0, strlen($charset) - 1)];
        }
        return $key;
    }

    /**
     * @param $xml
     * @param $url
     * @param int $second
     * @return bool|string
     * @throws Exception
     */
    private static function postXmlCurl($xml, $url, int $second = 30)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        /*if (CURL_PROXY_HOST != '0.0.0.0' && CURL_PROXY_PORT != 0) {
            curl_setopt($ch, CURLOPT_PROXY, CURL_PROXY_HOST);
            curl_setopt($ch, CURLOPT_PROXYPORT, CURL_PROXY_PORT);
        }*/
        curl_setopt($ch, CURLOPT_URL, $url);
        if (stripos($url, 'https://') !== false) {
            curl_setopt($ch, CURLOPT_SSLVERSION, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        } else {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        }
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);

        $data = curl_exec($ch);
        if ($data) {
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            throw new Exception('curl出错，错误码:' . $error);
        }
    }
}

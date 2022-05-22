<?php

namespace wxpay;

class UnifiedOrder extends DataBase
{
    public function setAppid($value)
    {
        $this->values['appid'] = $value;
    }

    public function setMchId($value)
    {
        $this->values['mch_id'] = $value;
    }

    public function setNonceStr($value)
    {
        $this->values['nonce_str'] = $value;
    }

    public function setBody($value)
    {
        $this->values['body'] = $value;
    }

    public function isBodySet()
    {
        return array_key_exists('body', $this->values);
    }

    public function setAttach($value)
    {
        $this->values['attach'] = $value;
    }

    public function setOutTradeNo($value)
    {
        $this->values['out_trade_no'] = $value;
    }

    public function isOutTradeNoSet()
    {
        return array_key_exists('out_trade_no', $this->values);
    }

    public function setTotalFee($value)
    {
        $this->values['total_fee'] = $value;
    }

    /*public function setFeeType($value){
        $this->values['fee_type'] = $value;
    }*/

    public function isTotalFeeSet()
    {
        return array_key_exists('total_fee', $this->values);
    }

    public function setSpbillCreateIp($value)
    {
        $this->values['spbill_create_ip'] = $value;
    }

    public function setGoodsTag($value)
    {
        $this->values['goods_tag'] = $value;
    }

    public function setProfitSharing($value)
    {
        $this->values['profit_sharing'] = $value;
    }

    public function setNotifyUrl($value)
    {
        $this->values['notify_url'] = $value;
    }

    public function setTradeType($value)
    {
        $this->values['trade_type'] = $value;
    }

    public function getTradeType()
    {
        return $this->values['trade_type'];
    }

    public function isTradeTypeSet()
    {
        return array_key_exists('trade_type', $this->values);
    }

    public function setProductId($value)
    {
        $this->values['product_id'] = $value;
    }

    public function isProductIdSet()
    {
        return array_key_exists('product_id', $this->values);
    }

    public function setOpenid($value)
    {
        $this->values['openid'] = $value;
    }

    public function isOpenidSet()
    {
        return array_key_exists('openid', $this->values);
    }
}

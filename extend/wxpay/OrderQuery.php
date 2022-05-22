<?php

namespace wxpay;

class OrderQuery extends DataBase
{
    public function setAppid($value)
    {
        $this->values['appid'] = $value;
    }

    public function setMchId($value)
    {
        $this->values['mch_id'] = $value;
    }

    public function setTransactionId($value)
    {
        $this->values['transaction_id'] = $value;
    }

    public function isTransactionIdSet()
    {
        return array_key_exists('transaction_id', $this->values);
    }

    public function isOutTradeNoSet()
    {
        return array_key_exists('out_trade_no', $this->values);
    }

    public function setNonceStr($value)
    {
        $this->values['nonce_str'] = $value;
    }
}

<?php

namespace wxpay;

class JsApi extends DataBase
{
    public function setAppid($value)
    {
        $this->values['appId'] = $value;
    }

    public function setTimeStamp($value)
    {
        $this->values['timeStamp'] = $value . '';
    }

    public function setNonceStr($value)
    {
        $this->values['nonceStr'] = $value;
    }

    public function setPackage($value)
    {
        $this->values['package'] = $value;
    }

    public function setSignType($value)
    {
        $this->values['signType'] = $value;
    }

    public function setPaySign($value)
    {
        $this->values['paySign'] = $value;
    }
}

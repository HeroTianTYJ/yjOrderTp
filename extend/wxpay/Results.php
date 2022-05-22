<?php

namespace wxpay;

use Exception;

class Results extends DataBase
{
    /**
     * @return bool
     * @throws Exception
     */
    public function checkSign()
    {
        if (!$this->isSignSet()) {
            throw new Exception('签名错误！');
        }
        if ($this->getSign() == $this->makeSign()) {
            return true;
        }
        throw new Exception('签名错误！');
    }

    /**
     * @param $xml
     * @return array
     * @throws Exception
     */
    public static function init($xml)
    {
        $obj = new self();
        $obj->fromXml($xml);
        if ($obj->values['return_code'] != 'SUCCESS') {
            return $obj->getValues();
        }
        $obj->checkSign();
        return $obj->getValues();
    }
}

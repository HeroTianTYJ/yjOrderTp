<?php

namespace wxpay;

use Exception;

class DataBase
{
    protected array $values = [];

    public function setSign()
    {
        $sign = $this->makeSign();
        $this->values['sign'] = $sign;
        return $sign;
    }

    public function getSign()
    {
        return $this->values['sign'];
    }

    public function isSignSet()
    {
        return array_key_exists('sign', $this->values);
    }

    /**
     * @return string
     * @throws Exception
     */
    public function toXml()
    {
        if (count($this->values) <= 0) {
            throw new Exception('数组数据异常！');
        }
        $xml = '<xml>';
        foreach ($this->values as $key => $val) {
            $xml .= is_numeric($val) ? '<' . $key . '>' . $val . '</' . $key . '>' : '<' . $key . '><![CDATA[' . $val .
                ']]></' . $key . '>';
        }
        $xml .= '</xml>';
        return $xml;
    }

    /**
     * @param $xml
     * @return array|mixed
     * @throws Exception
     */
    public function fromXml($xml)
    {
        if (!$xml) {
            throw new Exception('xml数据异常！');
        }
        if (version_compare(PHP_VERSION, '8.0.0', '<')) {
            libxml_disable_entity_loader(true);
        }
        $this->values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $this->values;
    }

    public function toUrlParams()
    {
        $buff = '';
        foreach ($this->values as $k => $v) {
            if ($k != 'sign' && $v != '' && !is_array($v)) {
                $buff .= $k . '=' . $v . '&';
            }
        }
        return trim($buff, '&');
    }

    public function makeSign()
    {
        ksort($this->values);
        return strtoupper(md5($this->toUrlParams() . '&key=' . KEY));
    }

    public function getValues()
    {
        return $this->values;
    }
}

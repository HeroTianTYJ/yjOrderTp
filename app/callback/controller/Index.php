<?php

namespace app\callback\controller;

use app\callback\model;
use think\facade\Config;
use think\facade\Request;
use wxpay\PayNotifyCallBack;
use yjrj\Alipay;

class Index extends Base
{
    private array $payScene = [
        0 => ['COUPON' => 1, 'ALIPAYACCOUNT' => 2, 'POINT' => 3, 'DISCOUNT' => 4, 'PCARD' => 5, 'FINANCEACCOUNT' => 6,
              'MCARD' => 7, 'MDISCOUNT' => 8, 'MCOUPON' => 9, 'PCREDIT' => 10],
        1 => ['JSAPI' => 1, 'NATIVE' => 2, 'APP' => 3, 'MWEB' => 4]
    ];

    public function alipayNotify()
    {
        if (
            (new Alipay([
                'app_id' => Config::get('system.alipay_app_id'),
                'merchant_private_key' => Config::get('system.alipay_merchant_private_key'),
                'public_key' => Config::get('system.alipay_public_key'),
                'method' => in_array(device(), ['android', 'iphone']) ? 'wap' : 'page'
            ]))->check($_POST)
        ) {
            (new model\Order())->modify(
                explode('_', Request::post('out_trade_no'))[1],
                2,
                Request::post('trade_no'),
                $this->payScene[0][json_decode(Request::post('fund_bill_list'))[0]->fundChannel],
                strtotime(Request::post('gmt_payment'))
            );
            return 'success';
        } else {
            return 'fail';
        }
    }

    public function wxpayNotify()
    {
        include ROOT_DIR . '/extend/wxpay/config.php';
        $result = (array)simplexml_load_string(file_get_contents('php://input'), 'SimpleXMLElement', LIBXML_NOCDATA);
        if ((new PayNotifyCallBack())->queryOrder($result['transaction_id'])) {
            (new model\Order())->modify(
                explode('-', $result['out_trade_no'])[1],
                3,
                $result['transaction_id'],
                $this->payScene[1][$result['trade_type']],
                strtotime($result['time_end'])
            );
            return '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
        }
        return '';
    }
}

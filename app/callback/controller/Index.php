<?php

namespace app\callback\controller;

use app\callback\model;
use think\facade\Config;
use think\facade\Request;
use yjrj\Alipay;
use yjrj\WechatPay;

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
                'public_key' => Config::get('system.alipay_public_key')
            ]))->check($_POST)
        ) {
            (new model\Order())->modify(
                explode('_', Request::post('out_trade_no'))[1],
                2,
                Request::post('trade_no'),
                $this->payScene[0][json_decode(Request::post('fund_bill_list', '', 'stripslashes'))[0]->fundChannel],
                strtotime(Request::post('gmt_payment'))
            );
            return 'success';
        } else {
            return 'fail';
        }
    }

    public function wechatPayNotify()
    {
        $notify = (new WechatPay([
            'app_id' => Config::get('system.wechat_pay_app_id'),
            'mch_id' => Config::get('system.wechat_pay_mch_id'),
            'key' => Config::get('system.wechat_pay_key')
        ]))->notify();
        if ($notify) {
            (new model\Order())->modify(
                explode('-', $notify['out_trade_no'])[1],
                3,
                $notify['transaction_id'],
                $this->payScene[1][$notify['trade_type']],
                $notify['success_time']
            );
        }
        return '';
    }
}

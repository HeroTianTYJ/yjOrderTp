<?php

namespace app\index\controller;

use app\index\model;
use think\facade\Request;
use think\facade\Config;
use think\facade\View;
use yjrj\Alipay;
use yjrj\WechatPay;

class Pay extends Base
{
    public function alipay()
    {
        if (Request::param('order_id')) {
            $orderOne = (new model\Order())->one();
            if (!$orderOne) {
                return $this->failed('不存在此订单！');
            }
            if ($orderOne['order_state_id'] != 1) {
                return $this->failed('此订单无法支付！');
            }
            $productOne = (new model\Product())->one($orderOne['product_id']);
            View::assign(['Pay' => in_array(device(), ['android', 'iphone', 'windows', 'mac']) ?
                (new Alipay($this->alipayConfig()))->pay(
                    [
                        'body' => '',
                        'subject' => $productOne ? $productOne['name'] : '',
                        'out_trade_no' => time() . '_' . Request::param('order_id'),
                        'total_amount' => $orderOne['price'] * $orderOne['count']
                    ],
                    [
                        'return' => Config::get('url.web1') . Config::get('system.index_php') . 'pay/alipayReturn.html',
                        'notify' => Config::get('url.web1') . 'callback.php/index/alipayNotify.html'
                    ]
                ) : '']);
            return $this->view();
        } else {
            return $this->failed('非法操作！');
        }
    }

    public function alipayReturn()
    {
        if ((new Alipay($this->alipayConfig()))->check(Request::get())) {
            $orderId = explode('_', Request::get('out_trade_no'))[1];
            $orderOne = (new model\Order())->one($orderId);
            $templateOne = (new model\Template())->one($orderOne['template_id']);
            return $this->succeed('', str_replace('{order_id}', $orderId, $templateOne['success2']));
        } else {
            return $this->failed('很遗憾，订单支付失败，如果您确定已经支付，请联系客服解决！', 0);
        }
    }

    private function alipayConfig()
    {
        return [
            'app_id' => Config::get('system.alipay_app_id'),
            'merchant_private_key' => Config::get('system.alipay_merchant_private_key'),
            'public_key' => Config::get('system.alipay_public_key'),
            'method' => in_array(device(), ['android', 'iphone']) ? 'wap' : 'page'
        ];
    }

    public function wechatPay()
    {
        if (Request::param('order_id')) {
            $orderOne = (new model\Order())->one();
            if (!$orderOne) {
                return $this->failed('不存在此订单！');
            }
            if ($orderOne['order_state_id'] != 1) {
                return $this->failed('此订单无法支付！');
            }
            $productOne = (new model\Product())->one($orderOne['product_id']);
            $WechatPay = new WechatPay([
                'app_id' => Config::get('system.wechat_pay_app_id'),
                'app_secret' => Config::get('system.wechat_pay_app_secret'),
                'mch_id' => Config::get('system.wechat_pay_mch_id'),
                'cert_serial_number' => Config::get('system.wechat_pay_cert_serial_number'),
                'cert_private_key' => Config::get('system.wechat_pay_cert_private_key')
            ]);
            $wechatPayParam = [
                'out_trade_no' => time() . '-' . Request::param('order_id'),
                'description' => $productOne ? $productOne['name'] : '',
                'notify_url' => Config::get('url.web1') . 'callback.php/index/wechatPayNotify.html',
                'total' => $orderOne['price'] * $orderOne['count'] * 100
            ];
            if (in_array(device(), ['windows', 'mac', 'windowsWechat', 'macWechat'])) {
                View::assign(['Url' => $WechatPay->native($wechatPayParam), 'jsApiParameters' => '']);
                return $this->view();
            } elseif (in_array(device(), ['androidWechat', 'iphoneWechat'])) {
                View::assign(['Url' => '', 'jsApiParameters' => $WechatPay->jsapi($wechatPayParam)]);
                return $this->view();
            } else {
                return '<script type="text/javascript">window.location.href="' .
                    $WechatPay->h5($wechatPayParam) . '&redirect_url=' .
                    urldecode(Config::get('url.web1') . Config::get('system.index_php') . 'pay/wechatPayH5/order_id/' .
                        Request::param('order_id') . '.html') . '";</script>';
            }
        } else {
            return $this->failed('非法操作！');
        }
    }

    public function wechatPayH5()
    {
        if (Request::param('order_id')) {
            $orderOne = (new model\Order())->one();
            if (!$orderOne) {
                return $this->failed('不存在此订单！');
            }
            if ($orderOne['order_state_id'] != 1) {
                return $this->failed('此订单无法支付！');
            }
            return $this->view();
        } else {
            return $this->failed('非法操作！');
        }
    }

    public function wechatPayAjax()
    {
        if (Request::isAjax()) {
            return (new model\Order())->one(Request::post('order_id'))['order_state_id'];
        } else {
            return showTip('非法操作！', 0);
        }
    }

    public function wechatPayTip()
    {
        if (Request::get('order_id')) {
            $orderOne = (new model\Order())->one(Request::get('order_id'));
            $templateOne = (new model\Template())->one($orderOne['template_id']);
            return $this->succeed('', str_replace('{order_id}', Request::get('order_id'), $templateOne['success2']));
        }
        return '';
    }
}

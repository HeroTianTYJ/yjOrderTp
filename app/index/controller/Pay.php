<?php

namespace app\index\controller;

use app\index\model;
use Exception;
use think\facade\Request;
use think\facade\Config;
use think\facade\View;
use wxpay\Api;
use wxpay\JsApiPay;
use wxpay\UnifiedOrder;
use yjrj\Alipay;

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
            return $this->succeed('', str_replace('{order_id}', $orderId, $templateOne['success2']), 0, 2);
        } else {
            return $this->failed('很遗憾，订单支付失败，如果您确定已经支付，请联系客服解决！', 0, 2);
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

    public function wxpay()
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
            include ROOT_DIR . '/extend/wxpay/config.php';
            $UnifiedOrder = new UnifiedOrder();
            $UnifiedOrder->setBody($productOne ? $productOne['name'] : '');
            $UnifiedOrder->setAttach($productOne ? $productOne['name'] : '');
            $UnifiedOrder->setOutTradeNo(time() . '-' . Request::param('order_id'));
            $UnifiedOrder->setTotalFee($orderOne['price'] * $orderOne['count'] * 100);
            $UnifiedOrder->setGoodsTag($productOne ? $productOne['name'] : '');
            $UnifiedOrder->setNotifyUrl(Config::get('url.web1') . 'callback.php/index/wxpayNotify.html');
            $UnifiedOrder->setProfitSharing('N');
            if (in_array(device(), ['windows', 'mac', 'windowsWechat', 'macWechat'])) {
                $UnifiedOrder->setTradeType('NATIVE');
                $UnifiedOrder->setProductId($orderOne['product_id']);
                try {
                    View::assign([
                        'Url' => Api::unifiedOrder($UnifiedOrder)['code_url'],
                        'jsApiParameters' => ''
                    ]);
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
                return $this->view();
            } elseif (in_array(device(), ['androidWechat', 'iphoneWechat'])) {
                $UnifiedOrder->setTradeType('JSAPI');
                $JsApiPay = new JsApiPay();
                $UnifiedOrder->setOpenid($JsApiPay->getOpenid());
                try {
                    View::assign([
                        'Url' => '',
                        'jsApiParameters' => $JsApiPay->getJsApiParameters(Api::unifiedOrder($UnifiedOrder))
                    ]);
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
                return $this->view();
            } else {
                $UnifiedOrder->setTradeType('MWEB');
                try {
                    return '<script type="text/javascript">window.location.href="' .
                        Api::unifiedOrder($UnifiedOrder)['mweb_url'] . '&redirect_url=' .
                        urldecode(Config::get('url.web1') . Config::get('system.index_php') . 'pay/wxpayH5/order_id/' .
                            Request::param('order_id') . '.html') . '";</script>';
                } catch (Exception $e) {
                    return $e->getMessage();
                }
            }
        } else {
            return $this->failed('非法操作！');
        }
    }

    public function wxpayH5()
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

    public function wxpayAjax()
    {
        if (Request::isAjax()) {
            return (new model\Order())->one(Request::post('order_id'))['order_state_id'];
        } else {
            return showTip('非法操作！', 0);
        }
    }

    public function wxpayTip()
    {
        if (Request::get('order_id')) {
            $orderOne = (new model\Order())->one(Request::get('order_id'));
            $templateOne = (new model\Template())->one($orderOne['template_id']);
            return
                $this->succeed('', str_replace('{order_id}', Request::get('order_id'), $templateOne['success2']), 0, 2);
        }
        return '';
    }
}

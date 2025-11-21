<?php

namespace app\index\controller;

use app\index\model;
use think\facade\Config;
use think\facade\Request;
use think\facade\View;

class Order extends Base
{
    public function index()
    {
    }

    public function search()
    {
        if (Config::get('system.order_search') == '0') {
            return $this->failed('本站未开启查单服务！');
        }
        $Template = new model\Template();
        if (Config::get('system.order_search_step') == '0') {
            $templateOne = $Template->one(Request::get('template_id'));
            if (!$templateOne) {
                return $this->failed('不存在此下单模板！');
            }
            if ($templateOne['is_show_search'] == 0) {
                return $this->failed('此下单模板未开启查单服务！');
            }
        }
        if (Request::get('keyword') == '') {
            return $this->failed('查询关键词不得为空！');
        }
        if (!in_array(Request::get('field'), [1, 2, 3])) {
            return $this->failed('查询参数有误！');
        }
        $orderAll = (new model\Order())->all();
        if ($orderAll) {
            $Manager = new model\Manager();
            $Product = new model\Product();
            $Express = new model\Express();
            $OrderState = new model\OrderState();
            foreach ($orderAll as $key => $value) {
                if ($value['manager_id']) {
                    $managerOne = $Manager->one($value['manager_id']);
                    $orderAll[$key]['manager'] = $managerOne ? $managerOne['name'] : '此管理员已被删除';
                } else {
                    $orderAll[$key]['manager'] = '终端用户';
                }

                $productOne = $Product->one($value['product_id']);
                $orderAll[$key]['product'] = $productOne ? '<span style="color:' . $productOne['color'] . ';">' .
                    $productOne['name'] . '（' . $productOne['price'] . '元）</span>' : '此商品已被删除';
                $orderAll[$key]['total'] = number_format($value['price'] * $value['count'], 2, '.', '');

                if ($value['express_id']) {
                    $expressOne = $Express->one($value['express_id']);
                    $orderAll[$key]['express_name'] = $expressOne ? $expressOne['name'] : '此快递公司已被删除';
                    $orderAll[$key]['express_code'] = $expressOne ? $expressOne['code'] : '';
                } else {
                    $orderAll[$key]['express_name'] = $orderAll[$key]['express_code'] = '';
                }

                $templateOne = $Template->one($value['template_id']);
                $orderAll[$key]['template'] = $templateOne ? $templateOne['name'] : '此模板已被删除';

                $orderStateOne = $OrderState->one($value['order_state_id']);
                $orderAll[$key]['order_state'] = $orderStateOne ?
                    '<span style="color:' . $orderStateOne['color'] . ';">' . $orderStateOne['name'] . '</span>' :
                    '此状态已被删除';

                $orderAll[$key]['ip'] = $value['ip'] . ' ' . ipGeolocation($value['ip']);

                $orderAll[$key]['pay_url'] = $this->payUrl($value['order_id']);
            }
            View::assign([
                'All' => $orderAll,
                'Total' => count($orderAll)
            ]);
            return $this->view();
        } else {
            return $this->failed('抱歉，没有查询到任何结果！');
        }
    }

    private function payUrl($orderId)
    {
        return [
            'alipay' => Config::get('url.web1') . Config::get('system.index_php') . 'pay/alipay/order_id/' . $orderId .
                '.html',
            'wechat_pay' => Config::get('url.web1') . Config::get('system.index_php') . 'pay/wechatPay/order_id/' .
                $orderId . '.html'
        ];
    }
}

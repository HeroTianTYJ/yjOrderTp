<?php

namespace app\admin\controller;

use app\admin\model;
use app\admin\library\Html;
use app\common\controller\Auth;
use Exception;
use PHPMailer\PHPMailer;
use think\facade\Config;
use think\facade\Request;
use think\facade\View;
use yjrj\QQWry;

class Order extends Base
{
    public function index()
    {
        $orderAll = (new model\Order())->all();
        if (Request::isAjax()) {
            foreach ($orderAll as $key => $value) {
                $orderAll[$key] = $this->listItem($value);
            }
            return $orderAll->items() ? apiResponse('', 1, $orderAll->items()) : '';
        }
        View::assign(['Total' => $orderAll->total()]);
        Html::product(Request::get('product_id'), 0, true);
        Html::manager2(Request::get('manager_id', -1));
        Html::orderPaymentSelect(Request::get('payment_id'));
        Html::alipayScene(Request::get('alipay_scene', -1));
        Html::wechatPayScene(Request::get('wechat_pay_scene', -1));
        Html::orderState(Request::get('order_state_id'));
        Html::express(Request::get('express_id'));
        Html::template2(Request::get('template_id'));
        return $this->view('order/index');
    }

    public function add()
    {
        return (new Auth())->addOrder();
    }

    public function state()
    {
        if (Request::isAjax()) {
            if (Request::get('action') == 'do') {
                $orderModify = (new model\Order())->modify2();
                return is_numeric($orderModify) ? apiResponse('订单状态修改成功！') : apiResponse($orderModify, 0);
            }
            Html::orderStateRadio();
            return $this->view();
        } else {
            return apiResponse('非法操作！', 0);
        }
    }

    public function express()
    {
        if (Request::isAjax()) {
            $Order = new model\Order();
            if (Request::get('action') == 'do') {
                $orderModify = $Order->modify3();
                return is_numeric($orderModify) ? apiResponse('快递单号修改成功！') : apiResponse($orderModify, 0);
            }
            $orderIds = '';
            foreach ($Order->all4() as $value) {
                $orderIds .= $value['order_id'] . '
';
            }
            View::assign(['OrderIds' => substr($orderIds, 0, -2)]);
            Html::express();
            return $this->view();
        } else {
            return apiResponse('非法操作！', 0);
        }
    }

    public function update()
    {
        if (Request::isAjax() && Request::post('id')) {
            $Order = new model\Order();
            $orderOne = $Order->one();
            if (!$orderOne) {
                return apiResponse('不存在此订单！', 0);
            }
            if (Request::get('action') == 'do') {
                $orderModify = $Order->modify();
                if (is_numeric($orderModify)) {
                    if (Request::post('send_mail')) {
                        if (Request::post('send_mail') == 1) {
                            $this->sendmail(
                                Request::post('email'),
                                Config::get('system.mail_pay_subject'),
                                Config::get('system.mail_pay_content')
                            );
                        } elseif (Request::post('send_mail') == 2) {
                            $this->sendmail(
                                Request::post('email'),
                                Config::get('system.mail_send_subject'),
                                Config::get('system.mail_send_content')
                            );
                        }
                    }
                    if (Request::post('send_sms') == 1) {
                        sendSms(
                            Request::post('tel'),
                            strip_tags($this->mail(Config::get('system.sms_backend_content')))
                        );
                    }
                    return apiResponse('订单修改成功！', 1, $this->listItem($Order->one()));
                } else {
                    return apiResponse($orderModify, 0);
                }
            }
            Html::template2($orderOne['template_id']);
            Html::product($orderOne['product_id'], 0, true);
            Html::express($orderOne['express_id']);
            Html::orderPaymentRadio($orderOne['payment_id']);
            Html::orderStateRadio($orderOne['order_state_id']);
            $orderOne['pay_url'] = $this->payUrl($orderOne['order_id']);
            View::assign(['One' => $orderOne]);
            return $this->view('order/update');
        } else {
            return apiResponse('非法操作！', 0);
        }
    }

    public function detail()
    {
        if (Request::post('id')) {
            $Order = new model\Order();
            $orderOne = $Order->one();
            if (!$orderOne) {
                return apiResponse('不存在此订单！', 0);
            }

            if ($orderOne['manager_id']) {
                $managerOne = (new model\Manager())->one($orderOne['manager_id']);
                $orderOne['manager'] = $managerOne ? $managerOne['name'] : '此管理员已被删除';
            } else {
                $orderOne['manager'] = '终端客户';
            }
            $templateOne = (new model\Template())->one($orderOne['template_id']);
            $orderOne['template'] = $templateOne ? $templateOne['name'] : '此模板已被删除';
            $productOne = (new model\Product())->one($orderOne['product_id']);
            $orderOne['product'] = $productOne ? $productOne['name'] : '此商品已被删除';
            $orderOne['total'] = number_format($orderOne['price'] * $orderOne['count'], 2, '.', '');
            $payScene = '';
            if ($orderOne['order_state_id'] != 1) {
                if ($orderOne['payment_id'] == 2) {
                    $payScene = Config::get('pay_scene.alipay.' . $orderOne['pay_scene'], '');
                } elseif ($orderOne['payment_id'] == 3) {
                    $payScene = Config::get('pay_scene.wechat_pay.' . $orderOne['pay_scene'], '');
                }
            }
            $orderOne['pay_scene'] = $payScene;
            $orderOne['pay_url'] = $this->payUrl($orderOne['order_id']);
            $orderStateOne = (new model\OrderState())->one($orderOne['order_state_id']);
            $orderOne['order_state'] = $orderStateOne ?
                '<span style="color:' . $orderStateOne['color'] . ';">' . $orderStateOne['name'] . '</span>' :
                '此状态已被删除';
            if ($orderOne['express_id']) {
                $expressOne = (new model\Express())->one($orderOne['express_id']);
                $orderOne['express_name'] = $expressOne ? $expressOne['name'] : '此快递公司已被删除';
                $orderOne['express_code'] = $expressOne ? $expressOne['code'] : '';
            } else {
                $orderOne['express_name'] = $orderOne['express_code'] = '';
            }

            View::assign(['One' => $orderOne]);
            return $this->view('order/detail');
        } else {
            return apiResponse('非法操作！', 0);
        }
    }

    public function delete()
    {
        if (Request::isAjax() && (Request::post('id') || Request::post('ids'))) {
            if (Config::get('app.demo')) {
                return apiResponse('演示站，订单无法删除！', 0);
            }
            $Order = new model\Order();
            if (Request::post('id')) {
                if (!$Order->one()) {
                    return apiResponse('不存在此订单！', 0);
                }
            } elseif (Request::post('ids')) {
                foreach (explode(',', Request::post('ids')) as $value) {
                    if (!$Order->one($value)) {
                        return apiResponse('不存在您勾选的订单！', 0);
                    }
                }
            }
            return $Order->recycle() ? apiResponse('订单已被移入回收站！') : apiResponse('订单移入回收站失败！', 0);
        } else {
            return apiResponse('非法操作！', 0);
        }
    }

    public function output()
    {
        if (Request::isAjax()) {
            $Order = new model\Order();
            if (Request::post('type') == 0) {
                return $this->outputDo($Order->all2(), Request::post('siwu', 0));
            } else {
                return $this->outputDo($Order->all3(), Request::post('siwu', 0));
            }
        } else {
            return apiResponse('非法操作！', 0);
        }
    }

    private function outputDo($orderAll, $type)
    {
        if ($type == 0) {
            $output = '"订单号","管理员","下单模板","姓名","订购商品","成交单价","订购数量","成交总价","联系电话","详细地址","备注","电子邮箱","下单IP",' .
                '"下单来路","下单时间","支付状态","支付订单号","支付场景","支付时间","订单状态","快递公司","快递单号",';
        } else {
            $output = '"客户编号","单位名称","单位简称","联系地址","邮政编码","联系人","联系人手机","用户电话","用户传真","所属省份","所属地市","网址","备注","是否客户",' .
                '"是否供应商","开户银行","帐号","拼音码","国家","区镇","邮箱",';
        }
        if ($orderAll) {
            $Manager = new model\Manager();
            $Template = new model\Template();
            $Product = new model\Product();
            $Express = new model\Express();
            $OrderState = new model\OrderState();
            foreach ($orderAll as $value) {
                if ($type == 0) {
                    if ($value['manager_id']) {
                        $managerOne = $Manager->one($value['manager_id']);
                        $managerName = $managerOne ? $managerOne['name'] : '此管理员/分销商已被删除';
                    } else {
                        $managerName = '终端客户';
                    }
                    $templateOne = $Template->one($value['template_id']);
                    $productOne = $Product->one($value['product_id']);
                    if ($value['express_id']) {
                        $expressOne = $Express->one($value['express_id']);
                        $expressName = $expressOne ? $expressOne['name'] : '此快递已被删除';
                    } else {
                        $expressName = '';
                    }
                    $orderStateOne = $OrderState->one($value['order_state_id']);
                    $payScene = '';
                    if ($value['order_state_id'] != 1) {
                        if ($value['payment_id'] == 2) {
                            $payScene = Config::get('pay_scene.alipay.' . $value['pay_scene'], '');
                        } elseif ($value['payment_id'] == 3) {
                            $payScene = Config::get('pay_scene.wechat_pay.' . $value['pay_scene'], '');
                        }
                    }
                    $output .= "\r\n" . '"\'' . $value['order_id'] . '","' . $managerName . '","' .
                        ($templateOne ? $templateOne['name'] : '此模板已被删除') . '","' . $value['name'] . '","' .
                        ($productOne ? $productOne['name'] . '（' . $productOne['price'] . '元）' : '此商品已被删除') . '","' .
                        $value['price'] . '元","' . $value['count'] . '","' .
                        number_format($value['price'] * $value['count'], 2, '.', '') . '元","\'' . $value['tel'] .
                        '","' . $value['province'] . ' ' . $value['city'] . ' ' . $value['county'] . ' ' .
                        $value['address'] . '","' . $value['note'] . '","' .
                        $value['email'] . '","' . ($value['ip'] ? $value['ip'] . ' -- ' .
                            QQWry::getAddress($value['ip']) : '') . '","' . htmlspecialchars_decode($value['referrer'])
                        . '","' . dateFormat($value['date']) . '","' . Config::get('payment.' . $value['payment_id']) .
                        '","\'' . $value['pay_id'] . '","' . $payScene . '","' . ($value['pay_date'] ?
                            dateFormat($value['pay_date']) : '') . '","' . ($orderStateOne ? $orderStateOne['name'] :
                            '此状态已被删除') . '","' . $expressName . '","' . $value['express_number'] . '",';
                } else {
                    $output .= "\r\n" . '"","","","' . $value['town'] . ' ' . $value['address'] . '","","' .
                        $value['name'] . '","","\'' . $value['tel'] . '","","' . $value['province'] . '","' .
                        $value['city'] . '","","' . $value['note'] . '","","","","","","","' . $value['county'] .
                        '","",';
                }
            }
        }
        return json_encode(['extension' => 'csv', 'filename' => 'order_' . date('YmdHis') . '.csv', 'file' => $output]);
    }

    private function listItem($item)
    {
        $item['order_id'] = keyword($item['order_id']);
        if ($item['manager_id']) {
            $managerOne = (new model\Manager())->one($item['manager_id']);
            $item['manager'] = $managerOne ? $managerOne['name'] : '此管理员已被删除';
        } else {
            $item['manager'] = '终端客户';
        }
        $templateOne = (new model\Template())->one($item['template_id']);
        $item['template'] = $templateOne ? $templateOne['name'] : '此模板已被删除';
        $item['name'] = keyword($item['name']);
        $productOne = (new model\Product())->one($item['product_id']);
        $item['product'] = $productOne ? $productOne['name'] . '（' . $productOne['price'] . '元' . '）' : '此商品已被删除';
        $item['total'] = number_format($item['price'] * $item['count'], 2, '.', '');
        $item['tel'] = keyword($item['tel']);
        $item['address'] = $item['province'] . ' ' . $item['city'] . ' ' . $item['county'] . ' ' . $item['town'] . ' ' .
            $item['address'];
        $item['address_truncate'] = keyword(truncate($item['address'], 0, 25));
        $item['email'] = keyword($item['email']);
        $item['ip'] = '<span title="' . QQWry::getAddress($item['ip']) . '">' . keyword($item['ip']) . '</span>';
        $item['date'] = dateFormat($item['date']);
        $item['payment'] = Config::get('payment.' . $item['payment_id']);
        $item['pay_id'] = keyword($item['pay_id']);
        $payScene = '';
        if ($item['order_state_id'] != 1) {
            if ($item['payment_id'] == 2) {
                $payScene = Config::get('pay_scene.alipay.' . $item['pay_scene'], '');
            } elseif ($item['payment_id'] == 3) {
                $payScene = Config::get('pay_scene.wechat_pay.' . $item['pay_scene'], '');
            }
        }
        $item['pay_scene'] = $payScene;
        $item['pay_date'] = $item['pay_date'] ? dateFormat($item['pay_date']) : '';
        $orderStateOne = (new model\OrderState())->one($item['order_state_id']);
        $item['order_state'] = $orderStateOne ?
            '<span style="color:' . $orderStateOne['color'] . ';">' . $orderStateOne['name'] . '</span>' :
            '此状态已被删除';
        $item['express'] = '';
        if ($item['express_id']) {
            $expressOne = (new model\Express())->one($item['express_id']);
            $item['express'] = ($expressOne ? $expressOne['name'] : '') .
                '<br><a href="https://www.kuaidi100.com/chaxun?com=' . ($expressOne ? $expressOne['code'] : '') .
                '&nu=' . $item['express_number'] . '" target="_blank">' . keyword($item['express_number']) . '</a>';
        }
        return $item;
    }

    private function sendmail($address, $subject, $content)
    {
        try {
            $Smtp = new model\Smtp();
            if ($Smtp->count() > 0) {
                $smtpOne = $Smtp->one2();
                if ($smtpOne) {
                    $PHPMailer = new PHPMailer();
                    $PHPMailer->Host = $smtpOne[0]['smtp'];
                    $PHPMailer->Port = $smtpOne[0]['port'];
                    $PHPMailer->Username = $PHPMailer->From = $smtpOne[0]['email'];
                    $PHPMailer->Password = $smtpOne[0]['pass'];
                    $PHPMailer->FromName = $smtpOne[0]['from_name'];
                    $PHPMailer->addAddress($address, $PHPMailer->FromName);
                    $PHPMailer->Subject = $this->mail($subject);
                    $PHPMailer->Body = $this->mail($content);
                    $PHPMailer->send();
                }
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    private function mail($content)
    {
        $productOne = (new model\Product())->one(Request::post('product_id'));
        $expressOne = [];
        if (Request::post('express_id')) {
            $expressOne = (new model\Express())->one(Request::post('express_id'));
        }
        $orderStateOne = (new model\OrderState())->one(Request::post('order_state_id'));
        $payScene = '';
        if (Request::post('order_state_id') != 1) {
            if (Request::post('payment_id') == 2) {
                $payScene = Config::get('pay_scene.alipay.' . Request::post('pay_scene'), '');
            } elseif (Request::post('payment_id') == 3) {
                $payScene = Config::get('pay_scene.wechat_pay.' . Request::post('pay_scene'), '');
            }
        }
        $payUrl = $this->payUrl(Request::post('order_id'));

        return str_replace([
            '{order_id}',
            '{product_name}',
            '{product_price}',
            '{product_count}',
            '{product_total}',
            '{name}',
            '{tel}',
            '{province}',
            '{city}',
            '{county}',
            '{town}',
            '{address}',
            '{note}',
            '{ip}',
            '{alipay_url}',
            '{wechat_pay_url}',
            '{payment}',
            '{pay_id}',
            '{pay_scene}',
            '{pay_date}',
            '{order_state}',
            '{express_name}',
            '{express_id}',
            '{express_url}',
            '{date}'
        ], [
            Request::post('order_id'),
            $productOne ? $productOne['name'] : '',
            $productOne ? $productOne['price'] : '',
            Request::post('count'),
            $productOne ? number_format($productOne['price'] * Request::post('count'), 2, '.', '') : '0.00',
            Request::post('name'),
            Request::post('tel'),
            Request::post('province2'),
            Request::post('city2'),
            Request::post('county2'),
            Request::post('town2'),
            Request::post('address'),
            Request::post('note'),
            Request::post('ip') . ' ' . QQWry::getAddress(Request::post('ip')),
            $payUrl['alipay'],
            $payUrl['wechat_pay'],
            Config::get('payment.' . Request::post('payment_id')),
            Request::post('pay_id'),
            $payScene,
            Request::post('pay_date') ? dateFormat(Request::post('pay_date')) : '',
            $orderStateOne ?
                '<span style="color:' . $orderStateOne['color'] . ';">' . $orderStateOne['name'] . '</span>' : '',
            $expressOne ? $expressOne['name'] : '',
            Request::post('express_number'),
            'https://www.kuaidi100.com/chaxun?com=' . ($expressOne ? $expressOne['code'] : '') . '&nu=' .
                Request::post('express_number'),
            dateFormat(Request::post('date'))
        ], $content);
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

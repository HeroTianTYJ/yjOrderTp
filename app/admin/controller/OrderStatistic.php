<?php

namespace app\admin\controller;

use app\admin\library\Html;
use app\admin\model;
use think\facade\Request;
use think\facade\View;

class OrderStatistic extends Base
{
    public function index()
    {
        if (Request::isAjax()) {
            $Order = new model\Order();

            $data = [];
            //今天
            $time = date('Y-m-d');
            $data[0]['time'] = '今天（' . $time . '）';
            $data[0]['data'] = $this->diyTime($Order->diyTime($time, $time));
            //昨天
            $time = date('Y-m-d', strtotime('-1 day'));
            $data[1]['time'] = '昨天（' . $time . '）';
            $data[1]['data'] = $this->diyTime($Order->diyTime($time, $time));
            //本周
            $time1 = date('Y-m-d', time() - date('w') * 86400);
            $time2 = date('Y-m-d', time() + (6 - date('w')) * 86400);
            $data[2]['time'] = '本周（' . $time1 . ' ～ ' . $time2 . '）';
            $data[2]['data'] = $this->diyTime($Order->diyTime($time1, $time2));
            //最近一周
            $time1 = date('Y-m-d', time() - 518400);
            $time2 = date('Y-m-d');
            $data[3]['time'] = '最近一周（' . $time1 . ' ～ ' . $time2 . '）';
            $data[3]['data'] = $this->diyTime($Order->diyTime($time1, $time2));
            //本月
            $time1 = date('Y-m') . '-01';
            $time2 = date('Y-m-t');
            $data[4]['time'] = '本月（' . $time1 . ' ～ ' . $time2 . '）';
            $data[4]['data'] = $this->diyTime($Order->diyTime($time1, $time2));
            //最近一月
            $time1 = date('Y-m-d', time() - 2592000);
            $time2 = date('Y-m-d');
            $data[5]['time'] = '最近一月（' . $time1 . ' ～ ' . $time2 . '）';
            $data[5]['data'] = $this->diyTime($Order->diyTime($time1, $time2));
            //今年
            $time1 = date('Y') . '-01-01';
            $time2 = date('Y') . '-12-31';
            $data[6]['time'] = '今年（' . $time1 . ' ～ ' . $time2 . '）';
            $data[6]['data'] = $this->diyTime($Order->diyTime($time1, $time2));
            //最近一年
            $time1 = date('Y-m-d', time() - 31449600);
            $time2 = date('Y-m-d');
            $data[7]['time'] = '最近一年（' . $time1 . ' ～ ' . $time2 . '）';
            $data[7]['data'] = $this->diyTime($Order->diyTime($time1, $time2));
            //总计
            $orderOlder = $Order->older();
            $orderNewer = $Order->newer();
            $data[8]['time'] = '总计（' .
                ($orderOlder ? timestampFormat(strtotime($orderOlder['create_time']), 'Y-m-d') : '') . ' ～ ' .
                ($orderNewer ? timestampFormat(strtotime($orderNewer['create_time']), 'Y-m-d') : '') . '）';
            $data[8]['data'] = $this->diyTime($Order->diyTime());

            return $data ? apiResponse('', 1, $data) : '';
        }
        View::assign(['Total' => 9]);
        Html::product(Request::get('product_id'), 0, true);
        Html::manager2(Request::get('manager_id', -1));
        Html::orderPaymentSelect(Request::get('payment_id'));
        Html::alipayScene(Request::get('alipay_scene', -1));
        Html::wechatPayScene(Request::get('wechat_pay_scene', -1));
        Html::orderState(Request::get('order_state_id'));
        Html::express(Request::get('express_id'));
        Html::template2(Request::get('template_id'));
        return $this->view();
    }

    public function day()
    {
        return $this->dayMonthYear('%Y年%m月%d日');
    }

    public function month()
    {
        return $this->dayMonthYear('%Y年%m月');
    }

    public function year()
    {
        return $this->dayMonthYear('%Y年');
    }

    public function output()
    {
        if (Request::isAjax()) {
            $output = '"时间","待支付数","待发货数","已发货数","已签收数","售后中数","交易关闭数","待支付金额","待发货金额","已发货金额","已签收金额","售后中金额",' .
                '"交易关闭金额","订单数","成交数","订单金额","成交金额",';
            switch (Request::post('type')) {
                case 'month':
                    $time = '%Y年%m月';
                    break;
                case 'year':
                    $time = '%Y年';
                    break;
                default:
                    $time = '%Y年%m月%d日';
            }
            foreach ((new model\Order())->dayMonthYear($time, false) as $value) {
                $output .= "\r\n" . '"' . $value['time'] . '","' . $value['count1'] . '","' . $value['count2'] . '","' .
                    $value['count3'] . '","' . $value['count4'] . '","' . $value['count5'] . '","' . $value['count6'] .
                    '","' . $value['sum1'] . '","' . $value['sum2'] . '","' . $value['sum3'] . '","' . $value['sum4'] .
                    '","' . $value['sum5'] . '","' . $value['sum6'] . '","' . ($value['count1'] + $value['count2'] +
                        $value['count3'] + $value['count4'] + $value['count5'] + $value['count6']) . '","' .
                    ($value['count1'] + $value['count2'] + $value['count3'] + $value['count4'] + $value['count5']) .
                    '","' . ($value['sum1'] + $value['sum2'] + $value['sum3'] + $value['sum4'] + $value['sum5'] +
                        $value['sum6']) . '","' . ($value['sum1'] + $value['sum2'] + $value['sum3'] + $value['sum4'] +
                        $value['sum5']) . '",';
            }
            return json_encode([
                'extension' => 'csv',
                'filename' => 'order_statistic_' . date('YmdHis') . '.csv',
                'file' => $output
            ]);
        } else {
            return apiResponse('非法操作！', 0);
        }
    }

    private function diyTime($diyTime)
    {
        $diyTime['sum1'] = $diyTime['sum1'] ?? '0.00';
        $diyTime['sum2'] = $diyTime['sum2'] ?? '0.00';
        $diyTime['sum3'] = $diyTime['sum3'] ?? '0.00';
        $diyTime['sum4'] = $diyTime['sum4'] ?? '0.00';
        $diyTime['sum5'] = $diyTime['sum5'] ?? '0.00';
        $diyTime['sum6'] = $diyTime['sum6'] ?? '0.00';
        $diyTime['sum7'] = number_format($diyTime['sum1'] + $diyTime['sum2'] + $diyTime['sum3'] + $diyTime['sum4'] +
            $diyTime['sum5'] + $diyTime['sum6'], 2, '.', '');
        $diyTime['sum8'] = number_format($diyTime['sum1'] + $diyTime['sum2'] + $diyTime['sum3'] + $diyTime['sum4'] +
            $diyTime['sum5'], 2, '.', '');
        $diyTime['count7'] = $diyTime['count1'] + $diyTime['count2'] + $diyTime['count3'] + $diyTime['count4'] +
            $diyTime['count5'] + $diyTime['count6'];
        $diyTime['count8'] = $diyTime['count1'] + $diyTime['count2'] + $diyTime['count3'] + $diyTime['count4'] +
            $diyTime['count5'];
        return $diyTime;
    }

    private function dayMonthYear($time)
    {
        $orderDayMonthYear = (new model\Order())->dayMonthYear($time);
        $orderDayMonthYearTotal = $orderDayMonthYear->total();
        if (Request::isAjax()) {
            $count1 = $count2 = $count3 = $count4 = $count5 = $count6 =
            $sum1 = $sum2 = $sum3 = $sum4 = $sum5 = $sum6 = 0;
            $data = $orderDayMonthYear->items();
            foreach ($orderDayMonthYear as $key => $value) {
                $count1 += $value['count1'];
                $count2 += $value['count2'];
                $count3 += $value['count3'];
                $count4 += $value['count4'];
                $count5 += $value['count5'];
                $count6 += $value['count6'];
                $sum1 += $value['sum1'];
                $sum2 += $value['sum2'];
                $sum3 += $value['sum3'];
                $sum4 += $value['sum4'];
                $sum5 += $value['sum5'];
                $sum6 += $value['sum6'];
                $data[$key]['count7'] = $value['count1'] + $value['count2'] + $value['count3'] + $value['count4'] +
                    $value['count5'] + $value['count6'];
                $data[$key]['count8'] = $value['count1'] + $value['count2'] + $value['count3'] + $value['count4'] +
                    $value['count5'];
                $data[$key]['sum7'] = number_format($value['sum1'] + $value['sum2'] + $value['sum3'] + $value['sum4'] +
                    $value['sum5'] + $value['sum6'], 2, '.', '');
                $data[$key]['sum8'] = number_format($value['sum1'] + $value['sum2'] + $value['sum3'] + $value['sum4'] +
                    $value['sum5'], 2, '.', '');
            }
            $data[$orderDayMonthYearTotal]['time'] = '合计';
            $data[$orderDayMonthYearTotal]['count1'] = $count1;
            $data[$orderDayMonthYearTotal]['count2'] = $count2;
            $data[$orderDayMonthYearTotal]['count3'] = $count3;
            $data[$orderDayMonthYearTotal]['count4'] = $count4;
            $data[$orderDayMonthYearTotal]['count5'] = $count5;
            $data[$orderDayMonthYearTotal]['count6'] = $count6;
            $data[$orderDayMonthYearTotal]['count7'] = $count1 + $count2 + $count3 + $count4 + $count5 + $count6;
            $data[$orderDayMonthYearTotal]['count8'] = $count1 + $count2 + $count3 + $count4 + $count5;
            $data[$orderDayMonthYearTotal]['sum1'] = number_format($sum1, 2, '.', '');
            $data[$orderDayMonthYearTotal]['sum2'] = number_format($sum2, 2, '.', '');
            $data[$orderDayMonthYearTotal]['sum3'] = number_format($sum3, 2, '.', '');
            $data[$orderDayMonthYearTotal]['sum4'] = number_format($sum4, 2, '.', '');
            $data[$orderDayMonthYearTotal]['sum5'] = number_format($sum5, 2, '.', '');
            $data[$orderDayMonthYearTotal]['sum6'] = number_format($sum6, 2, '.', '');
            $data[$orderDayMonthYearTotal]['sum7'] = number_format($sum1 + $sum2 + $sum3 + $sum4 + $sum5 +
                $sum6, 2, '.', '');
            $data[$orderDayMonthYearTotal]['sum8'] = number_format($sum1 + $sum2 + $sum3 + $sum4 + $sum5, 2, '.', '');
            return $data ? apiResponse('', 1, $data) : '';
        }
        $param = '?';
        foreach (Request::get() as $key => $value) {
            if ($key != 'order') {
                $param .= '&' . $key . '=' . $value;
            }
        }
        View::assign(['Total' => $orderDayMonthYearTotal, 'Param' => $param]);
        Html::product(Request::get('product_id'), 0, true);
        Html::manager2(Request::get('manager_id', -1));
        Html::orderPaymentSelect(Request::get('payment_id'));
        Html::alipayScene(Request::get('alipay_scene', -1));
        Html::wechatPayScene(Request::get('wechat_pay_scene', -1));
        Html::orderState(Request::get('order_state_id'));
        Html::express(Request::get('express_id'));
        Html::template2(Request::get('template_id'));
        return $this->view();
    }
}

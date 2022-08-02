<?php

namespace app\callback\model;

use think\Model;

class Order extends Model
{
    //修改支付状态
    public function modify($orderId, $paymentId, $payId = 0, $payScene = '', $payDate = '')
    {
        return $this->where(['order_id' => $orderId])->update([
            'payment_id' => $paymentId,
            'pay_id' => $payId,
            'pay_scene' => $payScene,
            'pay_date' => $payDate,
            'order_state_id' => 2
        ]);
    }
}

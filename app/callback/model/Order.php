<?php

namespace app\callback\model;

use think\Model;

class Order extends Model
{
    //修改支付状态
    public function modify($orderId = 0, $paymentId = 0, $payId = 0, $paySceneId = 0, $payTime = 0)
    {
        return $this->where(['order_id' => $orderId])->update([
            'payment_id' => $paymentId,
            'pay_id' => $payId,
            'pay_scene_id' => $paySceneId,
            'pay_time' => timestampFormat($payTime),
            'order_state_id' => 2
        ]);
    }
}

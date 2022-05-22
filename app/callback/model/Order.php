<?php

namespace app\callback\model;

use Exception;
use think\Model;

class Order extends Model
{
    //查询一条
    public function one($orderId = 0)
    {
        try {
            return $this->field('user_id,product_id,price,count,name,tel,province,city,county,town,address,' .
                'wxxcx_openid,date')
                ->where(['order_id' => $orderId])
                ->find();
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }

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

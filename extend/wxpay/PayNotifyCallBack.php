<?php

namespace wxpay;

use Exception;

class PayNotifyCallBack extends NotifyReply
{
    //查询订单
    public function queryOrder($transactionId)
    {
        $OrderQuery = new OrderQuery();
        $OrderQuery->setTransactionId($transactionId);
        try {
            $orderQuery = Api::orderQuery($OrderQuery);
        } catch (Exception $e) {
            $orderQuery = [];
        }
        return array_key_exists('return_code', $orderQuery) && array_key_exists('result_code', $orderQuery) &&
            $orderQuery['return_code'] == 'SUCCESS' && $orderQuery['result_code'] == 'SUCCESS';
    }
}

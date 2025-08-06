<?php

namespace app\index\model;

use Exception;
use think\facade\Config;
use think\facade\Request;
use think\Model;

class Order extends Model
{
    //前台查单
    public function all()
    {
        try {
            $map['is_recycle'] = 0;
            if (Config::get('system.order_search_step') == '0') {
                $map['template_id'] = Request::get('template_id');
            }
            if (Request::get('field') == 1) {
                $map['order_id'] = Request::get('keyword');
            } elseif (Request::get('field') == 2) {
                $map['name'] = Request::get('keyword');
            } elseif (Request::get('field') == 3) {
                $map['tel'] = Request::get('keyword');
            }
            return $this->field('id,order_id,manager_id,template_id,product_id,price,count,name,tel,province,city,' .
                'county,town,address,note,email,ip,referrer,payment_id,pay_id,pay_scene_id,pay_time,order_state_id,' .
                'express_id,express_number,create_time')
                ->where($map)
                ->order(['create_time' => 'DESC'])
                ->select()
                ->toArray();
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }

    //查询支付信息
    public function one($orderId = 0)
    {
        try {
            return $this->field('template_id,product_id,price,count,order_state_id')
                ->where(['order_id' => $orderId ?: Request::param('order_id'), 'is_recycle' => 0])
                ->find();
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];
        }
    }
}

<?php

namespace app\admin\validate;

use app\common\validate\Base;

class Order extends Base
{
    protected $rule = [
        'product_id' => 'require',
        'price' => 'require|price',
        'count' => 'require|number',
        'name' => 'require|min:2|max:20',
        'tel' => 'require|regex:/^[\d\-]{7,20}$/',
        'district_type' => 'require|between:0,1',
        'province' => 'require|max:10',
        'city' => 'require|max:15',
        'county' => 'require|max:15',
        'town' => 'max:25',
        'province2' => 'require|min:2|max:10',
        'city2' => 'require|min:2|max:15',
        'county2' => 'max:15',
        'town2' => 'max:25',
        'address' => 'require|min:5|max:200',
        'note' => 'max:255',
        'payment_id' => 'require|between:1,3',
        'order_state_id' => 'require',
        'email' => 'email',
        'express_number' => 'max:30',
    ];
    protected $message = [
        'product_id' => '请先在商品管理模块中添加一个商品！',
        'price' => '成交单价必须是数字！',
        'count' => '订购数量必须是数字！',
        'name' => '姓名不得小于2位或大于20位！',
        'tel' => '联系电话必须是数字和-号，且不得小于7位或大于20位！',
        'district_type' => '所在地区类型不合法！',
        'province.require' => '请选择省份！',
        'province.max' => '省份不得大于10位！',
        'city.require' => '请选择城市！',
        'city.max' => '城市不得大于15位！',
        'county.require' => '请选择区/县！',
        'county.max' => '区/县不得大于15位！',
        'town' => '乡镇/街道不得大于25位！',
        'province2' => '省份不得小于2位或大于10位！',
        'city2' => '城市不得小于2位或大于15位！',
        'county2' => '区/县不得大于15位！',
        'town2' => '乡镇/街道不得大于25位！',
        'address' => '详细地址不得小于5位或大于200位！',
        'note' => '备注不得大于255位！',
        'payment_id' => '支付方式不合法！',
        'order_state_id' => '请先在订单状态模块中添加一个订单状态！',
        'email' => '电子邮箱格式不合法！',
        'express_number' => '快递单号不得大于30位！',
    ];
}

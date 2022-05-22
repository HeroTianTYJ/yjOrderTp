<?php

namespace app\index\validate;

use think\Validate;

class Order extends Validate
{
    protected $rule = [
        'count' => 'require|number',
        'name' => 'require|min:2|max:20',
        'tel' => 'require|regex:/^[\d\-]{7,20}$/',
        'province' => 'require',
        'city' => 'require',
        'county' => 'require',
        //'town'=>'require',
        'province2' => 'require|min:2|max:10',
        'city2' => 'require|min:2|max:15',
        'county2' => 'max:15',
        'town2' => 'max:25',
        'address' => 'require|min:5|max:200',
        'post' => 'regex:/^\d{6}$/',
        'note' => 'max:255',
        'email' => 'email',
        'payment_id' => 'require|in:1,2,3',
    ];
    protected $message = [
        'count' => '订购数量必须是数字！',
        'name' => '姓名不得小于2位或大于20位！',
        'tel' => '联系电话必须是数字和-号，且不得小于7位或大于20位！',
        'province' => '请选择省份！',
        'city' => '请选择城市！',
        'county' => '请选择区/县！',
        //'town'=>'请选择乡镇/街道！',
        'province2' => '省份不得小于2位或大于10位！',
        'city2' => '城市不得小于2位或大于15位！',
        'county2' => '区/县不得大于15位！',
        'town2' => '乡镇/街道不得大于25位！',
        'address' => '详细地址不得小于5位或大于200位！',
        'post' => '邮政编码必须是6位的数字！',
        'note' => '备注不得大于250位！',
        'email' => '电子邮箱格式不合法！',
        'payment_id' => '支付方式有误，请重新选择！',
    ];
}

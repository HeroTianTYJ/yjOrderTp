<?php

namespace app\index\validate;

use app\common\validate\Base;

class Order extends Base
{
    protected $rule = [
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
        'email' => 'email',
        'payment_id' => 'require|in:1,2,3',
    ];
    protected $message = [
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
        'note' => '备注不得大于250位！',
        'email' => '电子邮箱格式不合法！',
        'payment_id' => '支付方式有误，请重新选择！',
    ];
}

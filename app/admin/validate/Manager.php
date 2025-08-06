<?php

namespace app\admin\validate;

use app\common\validate\Base;

class Manager extends Base
{
    protected $rule = [
        'name' => 'require|max:20',
        'pass' => 'require',
        'repass' => 'require|confirm:pass',
        'admin_mail' => 'require',
        'level_id' => 'require|between:1,3',
        'is_activation' => 'require|between:0,1',
        'order_permit_id' => 'require|between:1,3',
    ];
    protected $message = [
        'name' => '账号不得为空或大于20位！',
        'pass' => '密码不得为空！',
        'repass' => '两次输入的密码不相同！',
        'admin_mail' => '管理员邮箱不得为空！',
        'level_id' => '身份设置不合法！',
        'is_activation' => '激活设置不合法！',
        'order_permit_id' => '订单权限设置不合法！',
    ];
}

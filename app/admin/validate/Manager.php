<?php

namespace app\admin\validate;

use think\Validate;

class Manager extends Validate
{
    protected $rule = [
        'name' => 'require|max:20',
        'pass' => 'require',
        'repass' => 'require|confirm:pass',
        'admin_mail' => 'require',
    ];
    protected $message = [
        'name' => '帐号不得为空或大于20位！',
        'pass' => '密码不得为空！',
        'repass' => '两次输入的密码不相同！',
        'admin_mail' => '管理员邮箱不得为空！',
    ];
}

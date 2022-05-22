<?php

namespace app\admin\validate;

use think\Validate;

class Smtp extends Validate
{
    protected $rule = [
        'smtp' => 'require|max:20',
        'port' => 'require|number|between:1,65535',
        'email' => 'require|max:50|email',
        'user' => 'require|max:50|email',
        'pass' => 'require|max:50',
    ];
    protected $message = [
        'smtp' => 'SMTP服务器不得为空或大于20位！',
        'port' => 'SMTP端口必须是1到65535之间的数字！',
        'email' => '发信人邮件地址不得为空或大于50位，且必须为邮箱格式！',
        'user' => 'SMTP身份验证用户名不得为空或大于50位，且必须为邮箱格式！',
        'pass' => 'SMTP身份验证密码不得为空或大于50位！',
    ];
}

<?php

namespace app\admin\validate;

use app\common\validate\Base;

class Smtp extends Base
{
    protected $rule = [
        'smtp' => 'require|max:20',
        'port' => 'require|between:1,65535',
        'email' => 'require|max:50|email',
        'pass' => 'require|max:50',
        'from_name' => 'require|max:50',
    ];
    protected $message = [
        'smtp' => 'SMTP服务器不得为空或大于20位！',
        'port' => 'SMTP端口必须是1到65535之间的数字！',
        'email' => '发件人邮箱不得为空或大于50位，且必须为邮箱格式！',
        'pass' => '发件人密码不得为空或大于50位！',
        'from_name' => '发件人名称不得为空或大于50位！'
    ];
}

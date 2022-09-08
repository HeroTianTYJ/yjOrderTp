<?php

namespace app\admin\validate;

use app\common\validate\Base;

class OrderState extends Base
{
    protected $rule = [
        'name' => 'require|max:20',
        'color' => 'max:20',
    ];
    protected $message = [
        'name' => '状态名称不得为空或大于20位！',
        'color' => '状态颜色不得大于20位！',
    ];
}

<?php

namespace app\admin\validate;

use app\common\validate\Base;

class Express extends Base
{
    protected $rule = [
        'name' => 'require|max:20',
        'code' => 'require|max:25',
    ];
    protected $message = [
        'name' => '公司名称不得为空或大于20位！',
        'code' => '公司代码不得为空或大于25位！',
    ];
}

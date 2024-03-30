<?php

namespace app\admin\validate;

use app\common\validate\Base;

class ProductSort extends Base
{
    protected $rule = [
        'name' => 'require|max:20',
        'color' => 'max:20',
    ];
    protected $message = [
        'name' => '分类名称不得为空或大于20位！',
        'color' => '分类颜色不得大于20位！',
    ];
}

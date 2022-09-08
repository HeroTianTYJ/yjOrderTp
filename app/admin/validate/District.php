<?php

namespace app\admin\validate;

use app\common\validate\Base;

class District extends Base
{
    protected $rule = [
        'name' => 'require|max:25',
    ];
    protected $message = [
        'name' => '区划名称不得为空或大于25位！',
    ];
}

<?php

namespace app\admin\validate;

use app\common\validate\Base;

class PermitGroup extends Base
{
    protected $rule = [
        'name' => 'require|max:20',
    ];
    protected $message = [
        'name' => '权限组名称不得为空或大于20位！',
    ];
}

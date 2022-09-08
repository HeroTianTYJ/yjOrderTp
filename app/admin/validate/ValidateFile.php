<?php

namespace app\admin\validate;

use app\common\validate\Base;

class ValidateFile extends Base
{
    protected $rule = [
        'name' => 'require',
        'extension' => 'require|between:0,2',
        'content' => 'require',
    ];
    protected $message = [
        'name' => '文件名不得为空且不得包含特殊字符！',
        'extension' => '文件扩展名不合法！',
        'content' => '文件内容不得为空！',
    ];
}

<?php

namespace app\admin\validate;

use app\common\validate\Base;

class TemplateStyle extends Base
{
    protected $rule = [
        'bg_color' => 'require|max:20',
        'border_color' => 'require|max:20',
        'button_color' => 'require|max:20',
        'select_current_bg_color' => 'require|max:20',
    ];
    protected $message = [
        'bg_color' => '背景颜色不得为空或大于20位！',
        'border_color' => '边框颜色不得为空或大于20位！',
        'button_color' => '按钮颜色不得为空或大于20位！',
        'select_current_bg_color' => '下拉框选中背景颜色不得为空或大于20位！',
    ];
}

<?php

namespace app\admin\validate;

use app\common\validate\Base;

class Product extends Base
{
    protected $rule = [
        'name' => 'require|max:30',
        'product_sort_id' => 'require',
        'price' => 'require|price',
        'color' => 'max:20',
    ];
    protected $message = [
        'name' => '商品名称不得为空或大于30位！',
        'product_sort_id' => '请先在商品分类模块中添加一个分类！',
        'price' => '商品价格必须是数字！',
        'color' => '商品颜色不得大于20位！',
    ];
}

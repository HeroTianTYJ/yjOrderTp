<?php

namespace app\common\validate;

use think\Validate;

class Base extends Validate
{
    public function checkbox($value1 = '', $value2 = '')
    {
        return !array_diff(is_array($value1) ? $value1 : explode(',', $value1), explode(',', $value2));
    }

    public function price($value = 0)
    {
        return !!preg_match('/^([1-9]\d*|0)(\.\d{1,2})?$/', $value);
    }

    public function priceRequire($value = 0)
    {
        return $value && $this->price($value);
    }
}

<?php

namespace app\admin\model;

use app\admin\validate\ValidateFile as validate;
use think\facade\Request;

class ValidateFile
{
    //表单验证
    public function form()
    {
        $data = [
            'name' => str_replace(["'", '"', '\\', '/', ':', '*', '?', '<', '>', '|'], '', Request::post('name')),
            'extension' => Request::post('extension'),
            'content' => Request::post('content')
        ];
        $validate = new validate();
        if ($validate->check($data)) {
            return $data;
        } else {
            return $validate->getError();
        }
    }
}

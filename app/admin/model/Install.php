<?php

namespace app\admin\model;

use app\admin\validate\Install as validate;
use think\facade\Request;
use think\Model;

class Install extends Model
{
    protected $name = 'text';

    //第三步表单验证
    public function step3()
    {
        $data = [
            'web_name' => Request::post('web_name'),
            'manager_enter' => Request::post('manager_enter')
        ];
        $validate = new validate();
        if ($validate->scene('step3')->check($data)) {
            if (substr(Request::post('manager_enter'), -4) != '.php') {
                return '后台入口必须以.php结尾！';
            }
            if (Request::post('manager_enter', '', 'strtolower') == 'admin.php') {
                return '后台入口不得是admin.php！';
            }
            if (is_file(ROOT_DIR . '/' . Request::post('manager_enter'))) {
                return '系统根目录中已存在' . Request::post('manager_enter') . '文件，请重新指定一个后台入口！';
            }
            return 1;
        } else {
            return $validate->getError();
        }
    }
}

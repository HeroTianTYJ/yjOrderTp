<?php

namespace app\admin\model;

use app\admin\validate\System as validate;
use think\facade\Config;
use think\facade\Request;
use think\Model;

class System extends Model
{
    protected $name = 'text';

    //表单验证
    public function form()
    {
        $data = [
            'web_name' => Request::post('web_name'),
            'admin_mail' => Request::post('admin_mail'),
            'www' => Request::post('www', 0),
            'https' => Request::post('https', 0),
            'manager_enter' => Request::post('manager_enter'),
            'index_php' => Request::post('index_php'),
            'order_time' => Request::post('order_time'),
            'order_search' => Request::post('order_search'),
            'order_search_step' => Request::post('order_search_step'),
            'mail_order_subject' => Request::post('mail_order_subject'),
            'mail_order_content' => Request::post('mail_order_content'),
            'mail_pay_subject' => Request::post('mail_pay_subject'),
            'mail_pay_content' => Request::post('mail_pay_content'),
            'mail_send_subject' => Request::post('mail_send_subject'),
            'mail_send_content' => Request::post('mail_send_content')
        ];
        $validate = new validate();
        if ($validate->check($data)) {
            if (Request::post('manager_enter') != Config::get('system.manager_enter')) {
                if (
                    !preg_match('/^\w+$/', substr(Request::post('manager_enter'), 0, -4)) ||
                    substr(Request::post('manager_enter'), -4) != '.php'
                ) {
                    return '管理员后台入口必须是英文、数字、下划线，且以.php结尾！';
                }
                if (Request::post('manager_enter', '', 'strtolower') == 'admin.php') {
                    return '管理员后台入口不得是admin.php！';
                }
                if (is_file(ROOT_DIR . '/' . Request::post('manager_enter'))) {
                    return '系统根目录中已存在' . Request::post('manager_enter') . '文件，请重新指定一个管理员后台入口！';
                }
            }
            return 1;
        } else {
            return $validate->getError();
        }
    }
}

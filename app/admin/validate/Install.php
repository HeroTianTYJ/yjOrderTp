<?php

namespace app\admin\validate;

use think\Validate;

class Install extends Validate
{
    protected $rule = [
        'db_host' => 'require',
        'db_port' => 'number',
        'db_name' => 'require',
        'db_user' => 'require',
        'db_pwd' => 'require',
        'db_prefix' => 'require',

        'web_name' => 'require',
        'manager_enter' => 'require',
    ];
    protected $message = [
        'db_host' => '数据库服务器不得为空！',
        'db_port' => '服务器端口必须是数字！',
        'db_name' => '数据库名不得为空！',
        'db_user' => '数据库用户名不得为空！',
        'db_pwd' => '数据库密码不得为空！',
        'db_prefix' => '数据表前缀不得为空！',

        'web_name' => '站点名称不得为空！',
        'manager_enter' => '后台入口不得为空！',
    ];
    protected $scene = [
        'step2' => ['db_host', 'db_port', 'db_name', 'db_user', 'db_pwd', 'db_prefix'],
        'step3' => ['web_name', 'manager_enter'],
    ];
}

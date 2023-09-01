<?php

namespace app\admin\validate;

use app\common\validate\Base;

class Install extends Base
{
    protected $rule = [
        'hostname' => 'require',
        'hostport' => 'number',
        'database' => 'require|notIn:information_schema,mysql,performance_schema,sys',
        'username' => 'require',
        'password' => 'require',
        'prefix' => 'require',

        'web_name' => 'require',
        'manager_enter' => 'require',
    ];
    protected $message = [
        'hostname' => '数据库服务器不得为空！',
        'hostport' => '服务器端口必须是数字！',
        'database.require' => '数据库名不得为空！',
        'database.notIn' => '此数据库名为保留名称！',
        'username' => '数据库用户名不得为空！',
        'password' => '数据库密码不得为空！',
        'prefix' => '数据表前缀不得为空！',

        'web_name' => '站点名称不得为空！',
        'manager_enter' => '后台入口不得为空！',
    ];
    protected $scene = [
        'step2' => ['hostname', 'hostport', 'database', 'username', 'password', 'prefix'],
        'step3' => ['web_name', 'manager_enter'],
    ];
}

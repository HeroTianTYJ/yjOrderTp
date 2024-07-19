<?php

namespace app\admin\controller;

use app\admin\model;
use app\common\controller\Auth;
use think\facade\Request;
use think\facade\Route;

class Install extends Base
{
    protected function initialize()
    {
        $this->loadConfig();
        if (file_exists(ROOT_DIR . '/data/install.lock')) {
            $this->error('安装锁定，已经安装过了，如果您确定要重新安装，请到服务器上删除：./data/install.lock。', 0);
        }
    }

    public function index()
    {
        if (Request::isPost()) {
            return $this->succeed(Route::buildUrl('/' . parse_name(Request::controller()) . '/step2'));
        }
        return $this->view();
    }

    public function step2()
    {
        if (Request::isPost()) {
            $installStep = (new model\Install())->step2();
            if (is_array($installStep)) {
                $output = "<?php

return [
    'default' => 'mysql',
    'connections' => [
        'mysql' => [
            'type' => 'mysql',  //数据库类型
            'hostname' => '" . str_replace("'", "\'", $installStep['hostname']) . "',  //数据库服务器
            'hostport' => '" . str_replace("'", "\'", $installStep['hostport']) . "',  //服务器端口
            'database' => '" . str_replace("'", "\'", $installStep['database']) . "',  //数据库名
            'username' => '" . str_replace("'", "\'", $installStep['username']) . "',  //数据库用户名
            'password' => '" . str_replace("'", "\'", $installStep['password']) . "',  //数据库密码
            'prefix' => '" . str_replace("'", "\'", $installStep['prefix']) . "',  //数据表前缀
            'charset' => '" . $installStep['charset'] . "',  //字符集
            'fields_cache' => true  //开启字段缓存
        ]
    ]
];
";
                if (!file_put_contents(ROOT_DIR . '/config/database.php', $output)) {
                    return $this->failed('配置文件修改失败，请检查config目录权限！');
                }

                if (
                    !$link = mysqli_connect(
                        $installStep['hostname'] . ($installStep['hostport'] ? ':' . $installStep['hostport'] : ''),
                        $installStep['username'],
                        $installStep['password']
                    )
                ) {
                    return $this->failed('无法连接数据库，请重新设置数据库信息！');
                }
                $databases = [];
                $result = mysqli_query($link, 'SHOW DATABASES');
                while ($row = mysqli_fetch_array($result)) {
                    $databases[] = $row['Database'];
                }
                if (in_array($installStep['database'], $databases)) {
                    if (
                        !mysqli_query($link, 'ALTER DATABASE `' . $installStep['database'] . '` CHARACTER SET ' .
                            $installStep['charset'] . ';')
                    ) {
                        return $this->failed('数据库字符集修改失败！');
                    }
                } elseif (
                    !mysqli_query($link, 'CREATE DATABASE `' . $installStep['database'] . '` CHARACTER SET ' .
                        $installStep['charset'] . ';')
                ) {
                    return $this->failed('数据库创建失败！');
                }
                mysqli_select_db($link, $installStep['database']);
                if (!mysqli_query($link, 'SET NAMES ' . $installStep['charset'] . ';')) {
                    return $this->failed('数据库字符集设置错误！');
                }
                foreach (
                    explode(';', str_replace(
                        'yjorder_',
                        $installStep['prefix'],
                        file_get_contents(ROOT_DIR . '/data/install.sql')
                    )) as $value
                ) {
                    if ($value && !mysqli_query($link, $value)) {
                        return $this->failed('数据库导入失败，请自行将data目录下的install.sql导入到' . $installStep['database'] .
                            '数据库，然后<a href="' . Route::buildUrl('/' . parse_name(Request::controller()) . '/step3') .
                            '">点击此处</a>进行下一步安装。', 0);
                    }
                }
                foreach (
                    explode(';', str_replace(
                        'yjorder_',
                        $installStep['prefix'],
                        file_get_contents(ROOT_DIR . '/data/district.sql')
                    )) as $value
                ) {
                    if ($value && !mysqli_query($link, $value)) {
                        return $this->failed('行政区划数据表导入失败，请自行将data目录下的district.sql导入到' . $installStep['database'] .
                            '数据库，然后<a href="' . Route::buildUrl('/' . parse_name(Request::controller()) . '/step3') .
                            '">点击此处</a>进行下一步安装。', 0);
                    }
                }
                mysqli_close($link);

                return $this->succeed(Route::buildUrl('/' . parse_name(Request::controller()) . '/step3'));
            } else {
                return $this->failed($installStep);
            }
        }
        return $this->view();
    }

    public function step3()
    {
        return (new Auth())->install();
    }

    public function openid()
    {
        return json_encode((new Auth())->openid());
    }
}
